<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use App\Services\GoogleCalendarService;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google_calendar.client_id' => 'client-id',
            'services.google_calendar.client_secret' => 'client-secret',
            'services.google_calendar.refresh_token' => 'refresh-token',
            'services.google_calendar.calendar_id' => 'clinic@example.test',
            'services.google_calendar.timezone' => 'UTC',
            'services.google_calendar.weekdays' => '1,2,3,4,5,6,7',
            'services.google_calendar.open_time' => '09:00',
            'services.google_calendar.close_time' => '11:00',
            'services.google_calendar.slot_interval' => 15,
            'services.google_calendar.booking_window_days' => 60,
            'services.google_calendar.durations' => [
                'appointment' => 30,
                'services' => 15,
                'location' => 15,
                'website_software' => 15,
                'callback' => 15,
                'other' => 30,
            ],
            'mail.clinic_lead_notification_email' => 'leads@example.test',
        ]);
    }

    public function test_availability_uses_the_duration_for_the_selected_need_and_excludes_busy_times(): void
    {
        $date = CarbonImmutable::now('UTC')->addDays(1)->toDateString();
        Http::fake(function (Request $request) use ($date) {
            if ($request->url() === 'https://oauth2.googleapis.com/token') {
                return Http::response(['access_token' => 'calendar-access-token'], 200);
            }

            if (str_contains($request->url(), '/freeBusy')) {
                return Http::response([
                    'calendars' => [
                        'clinic@example.test' => [
                            'busy' => [[
                                'start' => $date.'T09:30:00Z',
                                'end' => $date.'T10:00:00Z',
                            ]],
                        ],
                    ],
                ], 200);
            }

            return Http::response([], 404);
        });

        $response = $this->getJson('/api/appointments/availability?date='.$date.'&need=appointment');

        $response->assertOk()
            ->assertJsonPath('duration_minutes', 30)
            ->assertJsonPath('timezone', 'UTC');

        $this->assertCount(4, $response->json('slots'));
        $this->assertSame('09:00', $response->json('slots.0.time'));
        $this->assertSame('10:00', $response->json('slots.1.time'));
    }

    public function test_website_software_requests_can_book_a_meeting(): void
    {
        $date = CarbonImmutable::now('UTC')->addDays(1)->toDateString();
        Http::fake(function (Request $request) {
            if ($request->url() === 'https://oauth2.googleapis.com/token') {
                return Http::response(['access_token' => 'calendar-access-token'], 200);
            }

            if (str_contains($request->url(), '/freeBusy')) {
                return Http::response([
                    'calendars' => ['clinic@example.test' => ['busy' => []]],
                ], 200);
            }

            return Http::response([], 404);
        });

        $this->getJson('/api/appointments/availability?date='.$date.'&need=website_software')
            ->assertOk()
            ->assertJsonPath('duration_minutes', 15);
    }

    public function test_booking_creates_a_calendar_event_without_adding_visit_reason_to_event(): void
    {
        $createdEvent = null;
        Http::fake(function (Request $request) use (&$createdEvent) {
            if ($request->url() === 'https://oauth2.googleapis.com/token') {
                return Http::response(['access_token' => 'calendar-access-token'], 200);
            }

            if (str_contains($request->url(), '/freeBusy')) {
                return Http::response([
                    'calendars' => ['clinic@example.test' => ['busy' => []]],
                ], 200);
            }

            if ($request->method() === 'GET' && str_contains($request->url(), '/events/')) {
                return Http::response(['error' => ['code' => 404]], 404);
            }

            if ($request->method() === 'POST'
                && str_contains($request->url(), '/events')
                && !str_contains($request->url(), '/freeBusy')) {
                $createdEvent = $request->data();
                return Http::response(array_merge($createdEvent, ['htmlLink' => 'https://calendar.google.test/event']), 200);
            }

            return Http::response([], 404);
        });

        $bookingKey = '11111111-1111-4111-8111-111111111111';
        $start = CarbonImmutable::now('UTC')->addDay()->setTime(10, 0)->toIso8601String();
        $event = app(GoogleCalendarService::class)->book([
            'name' => 'Jordan Doe',
            'email' => 'jordan@example.test',
            'phone' => '',
            'need' => 'appointment',
            'meeting_type' => 'clinic',
            'location' => 'Downtown clinic',
            'start_at' => $start,
            'idempotency_key' => $bookingKey,
            'visit_reason' => 'Private health detail',
            'locale' => 'en',
        ]);

        $this->assertSame('11111111111141118111111111111111', $event['id']);
        $this->assertSame('UTC', $event['timezone']);

        $this->assertSame('Downtown clinic', $createdEvent['location']);
        $this->assertStringNotContainsString('Private health detail', $createdEvent['description']);
        $this->assertSame('jordan@example.test', $createdEvent['attendees'][0]['email']);
    }
}
