<?php

namespace App\Services;

use App\Exceptions\AppointmentSlotUnavailable;
use App\Exceptions\GoogleCalendarUnavailable;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    private const API_URL = 'https://www.googleapis.com/calendar/v3';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    public function availability(string $date, string $need): array
    {
        $this->ensureConfigured();

        $timezone = $this->timezone();
        $localDate = CarbonImmutable::createFromFormat('!Y-m-d', $date, $timezone);
        $duration = $this->duration($need);

        if (!$this->isBookableDate($localDate, $timezone)) {
            return ['duration_minutes' => $duration, 'slots' => [], 'timezone' => $timezone];
        }

        $open = CarbonImmutable::parse($date.' '.config('services.google_calendar.open_time'), $timezone);
        $close = CarbonImmutable::parse($date.' '.config('services.google_calendar.close_time'), $timezone);
        $busy = $this->busyPeriods($open, $close);
        $interval = (int) config('services.google_calendar.slot_interval');
        $slots = [];

        for ($start = $open; $start->addMinutes($duration)->lessThanOrEqualTo($close); $start = $start->addMinutes($interval)) {
            $end = $start->addMinutes($duration);
            if ($start->isFuture() && !$this->overlapsBusyPeriod($start, $end, $busy)) {
                $slots[] = [
                    'start_at' => $start->toIso8601String(),
                    'time' => $start->format('H:i'),
                ];
            }
        }

        return ['duration_minutes' => $duration, 'slots' => $slots, 'timezone' => $timezone];
    }

    public function book(array $booking): array
    {
        $this->ensureConfigured();

        $timezone = $this->timezone();
        $start = CarbonImmutable::parse($booking['start_at'])->setTimezone($timezone);
        $duration = $this->duration($booking['need']);
        $end = $start->addMinutes($duration);
        $this->ensureValidSlot($start, $end, $booking['need']);

        $eventId = str_replace('-', '', strtolower($booking['idempotency_key']));
        if (!preg_match('/^[0-9a-f]{32}$/', $eventId)) {
            throw new AppointmentSlotUnavailable('Invalid appointment request identifier.');
        }

        $token = $this->accessToken();
        $existingEvent = $this->findEvent($token, $eventId);
        if ($existingEvent !== null) {
            $this->ensureSameBooking($existingEvent, $booking['idempotency_key'], $start);
            return $this->eventResult($existingEvent, $start, $end);
        }

        $busy = $this->busyPeriods($start, $end, $token);
        if ($this->overlapsBusyPeriod($start, $end, $busy)) {
            throw new AppointmentSlotUnavailable('The selected appointment time is no longer available.');
        }

        $isSpanish = ($booking['locale'] ?? 'en') === 'es';
        $needLabels = [
            'appointment' => $isSpanish ? 'Solicitud de cita' : 'Appointment request',
            'services' => $isSpanish ? 'Consulta sobre servicios' : 'Services inquiry',
            'location' => $isSpanish ? 'Consulta sobre ubicación' : 'Location inquiry',
            'callback' => $isSpanish ? 'Solicitud de llamada' : 'Call-back request',
            'other' => $isSpanish ? 'Otra consulta' : 'Other inquiry',
        ];
        $summary = $isSpanish ? 'Cita con CarePoint' : 'CarePoint appointment';
        if ($booking['meeting_type'] === 'google_meet') {
            $summary .= $isSpanish ? ' (Google Meet)' : ' (Google Meet)';
        }

        $descriptionLines = [
            ($isSpanish ? 'Paciente: ' : 'Client: ').$booking['name'],
            ($isSpanish ? 'Motivo general: ' : 'Request type: ').$needLabels[$booking['need']],
        ];
        if ($booking['phone'] ?? null) {
            $descriptionLines[] = ($isSpanish ? 'Teléfono: ' : 'Phone: ').$booking['phone'];
        }

        $event = [
            'id' => $eventId,
            'summary' => $summary,
            'description' => implode("\n", $descriptionLines),
            'start' => [
                'dateTime' => $start->toRfc3339String(),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $end->toRfc3339String(),
                'timeZone' => $timezone,
            ],
            'extendedProperties' => [
                'private' => ['booking_key' => $booking['idempotency_key']],
            ],
        ];

        if ($booking['meeting_type'] === 'clinic') {
            $event['location'] = $booking['location'];
        } else {
            $event['conferenceData'] = [
                'createRequest' => [
                    'requestId' => $eventId,
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ];
        }

        if ($booking['email'] ?? null) {
            $event['attendees'] = [['email' => $booking['email']]];
        }

        $response = $this->calendarRequest(
            $token,
            'post',
            $this->eventsUrl().'?sendUpdates=all&conferenceDataVersion=1',
            $event
        );

        if ($response->status() === 409) {
            $existingEvent = $this->findEvent($token, $eventId);
            if ($existingEvent !== null) {
                $this->ensureSameBooking($existingEvent, $booking['idempotency_key'], $start);
                return $this->eventResult($existingEvent, $start, $end);
            }
        }

        if (!$response->successful()) {
            $this->logCalendarFailure('Google Calendar event creation failed.', $response->status());
            throw new GoogleCalendarUnavailable('Google Calendar could not create the appointment.');
        }

        return $this->eventResult($response->json(), $start, $end);
    }

    private function ensureConfigured(): void
    {
        foreach (['client_id', 'client_secret', 'refresh_token', 'calendar_id', 'timezone'] as $key) {
            if (!config('services.google_calendar.'.$key)) {
                throw new GoogleCalendarUnavailable('Google Calendar booking is not configured.');
            }
        }

        try {
            new \DateTimeZone($this->timezone());
        } catch (\Exception $exception) {
            throw new GoogleCalendarUnavailable('The appointment time zone is invalid.', 0, $exception);
        }

        $weekdays = explode(',', (string) config('services.google_calendar.weekdays'));
        if ((int) config('services.google_calendar.slot_interval') < 1
            || (int) config('services.google_calendar.booking_window_days') < 1
            || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', (string) config('services.google_calendar.open_time'))
            || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', (string) config('services.google_calendar.close_time'))
            || config('services.google_calendar.open_time') >= config('services.google_calendar.close_time')
            || $weekdays === []
            || count(array_filter($weekdays, fn (string $weekday): bool => ctype_digit($weekday) && (int) $weekday >= 1 && (int) $weekday <= 7)) !== count($weekdays)) {
            throw new GoogleCalendarUnavailable('The appointment slot interval is invalid.');
        }

        foreach (config('services.google_calendar.durations', []) as $duration) {
            if ((int) $duration < 1) {
                throw new GoogleCalendarUnavailable('An appointment duration is invalid.');
            }
        }
    }

    private function timezone(): string
    {
        return (string) config('services.google_calendar.timezone');
    }

    private function duration(string $need): int
    {
        $duration = (int) config('services.google_calendar.durations.'.$need, 0);
        if ($duration < 1) {
            throw new GoogleCalendarUnavailable('The appointment duration is not configured.');
        }

        return $duration;
    }

    private function isBookableDate(CarbonImmutable $date, string $timezone): bool
    {
        $today = CarbonImmutable::now($timezone)->startOfDay();
        $lastDate = $today->addDays((int) config('services.google_calendar.booking_window_days'));
        $weekdays = array_map('intval', explode(',', (string) config('services.google_calendar.weekdays')));
        $openTime = (string) config('services.google_calendar.open_time');
        $closeTime = (string) config('services.google_calendar.close_time');

        return $date->greaterThanOrEqualTo($today)
            && $date->lessThanOrEqualTo($lastDate)
            && in_array($date->isoWeekday(), $weekdays, true)
            && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $openTime) === 1
            && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $closeTime) === 1
            && $openTime < $closeTime;
    }

    private function ensureValidSlot(CarbonImmutable $start, CarbonImmutable $end, string $need): void
    {
        $date = $start->startOfDay();
        $timezone = $this->timezone();
        $open = CarbonImmutable::parse($date->format('Y-m-d').' '.config('services.google_calendar.open_time'), $timezone);
        $close = CarbonImmutable::parse($date->format('Y-m-d').' '.config('services.google_calendar.close_time'), $timezone);
        $interval = (int) config('services.google_calendar.slot_interval');
        $offsetMinutes = (int) (($start->getTimestamp() - $open->getTimestamp()) / 60);
        $durationMinutes = (int) (($end->getTimestamp() - $start->getTimestamp()) / 60);

        if (!$this->isBookableDate($date, $timezone)
            || $start->lessThan($open)
            || $end->greaterThan($close)
            || $start->isPast()
            || $offsetMinutes < 0
            || $offsetMinutes % $interval !== 0
            || $durationMinutes !== $this->duration($need)
            || $start->format('s') !== '00') {
            throw new AppointmentSlotUnavailable('The selected appointment time is invalid or unavailable.');
        }
    }

    private function busyPeriods(CarbonImmutable $start, CarbonImmutable $end, ?string $token = null): array
    {
        $token ??= $this->accessToken();
        $response = $this->calendarRequest($token, 'post', self::API_URL.'/freeBusy', [
            'timeMin' => $start->toRfc3339String(),
            'timeMax' => $end->toRfc3339String(),
            'timeZone' => $this->timezone(),
            'items' => [['id' => config('services.google_calendar.calendar_id')]],
        ]);

        if (!$response->successful()) {
            $this->logCalendarFailure('Google Calendar availability check failed.', $response->status());
            throw new GoogleCalendarUnavailable('Google Calendar availability could not be checked.');
        }

        $calendars = $response->json('calendars', []);
        $calendarId = config('services.google_calendar.calendar_id');
        if (!isset($calendars[$calendarId]['busy']) || !is_array($calendars[$calendarId]['busy'])) {
            $this->logCalendarFailure('Google Calendar returned an invalid availability response.', $response->status());
            throw new GoogleCalendarUnavailable('Google Calendar availability could not be checked.');
        }

        return $calendars[$calendarId]['busy'];
    }

    private function overlapsBusyPeriod(CarbonImmutable $start, CarbonImmutable $end, array $busyPeriods): bool
    {
        foreach ($busyPeriods as $period) {
            $busyStart = CarbonImmutable::parse($period['start']);
            $busyEnd = CarbonImmutable::parse($period['end']);
            if ($start->lessThan($busyEnd) && $end->greaterThan($busyStart)) {
                return true;
            }
        }

        return false;
    }

    private function accessToken(): string
    {
        try {
            $response = Http::asForm()->timeout(10)->post(self::TOKEN_URL, [
                'client_id' => config('services.google_calendar.client_id'),
                'client_secret' => config('services.google_calendar.client_secret'),
                'refresh_token' => config('services.google_calendar.refresh_token'),
                'grant_type' => 'refresh_token',
            ]);
        } catch (ConnectionException $exception) {
            Log::warning('Google Calendar OAuth connection failed.', [
                'exception_class' => get_class($exception),
                'exception_message' => $exception->getMessage(),
            ]);
            throw new GoogleCalendarUnavailable('Google Calendar authentication is unavailable.', 0, $exception);
        }

        if (!$response->successful() || !is_string($response->json('access_token'))) {
            $this->logCalendarFailure('Google Calendar token refresh failed.', $response->status());
            throw new GoogleCalendarUnavailable('Google Calendar authentication failed.');
        }

        return $response->json('access_token');
    }

    private function findEvent(string $token, string $eventId): ?array
    {
        $response = $this->calendarRequest($token, 'get', $this->eventsUrl().'/'.$eventId);
        if ($response->status() === 404) {
            return null;
        }

        if (!$response->successful()) {
            $this->logCalendarFailure('Google Calendar event lookup failed.', $response->status());
            throw new GoogleCalendarUnavailable('Google Calendar could not verify the appointment.');
        }

        return $response->json();
    }

    private function eventsUrl(): string
    {
        return self::API_URL.'/calendars/'.rawurlencode((string) config('services.google_calendar.calendar_id')).'/events';
    }

    private function calendarRequest(string $token, string $method, string $url, ?array $payload = null): Response
    {
        try {
            $request = Http::withToken($token)->timeout(10);
            return $payload === null ? $request->{$method}($url) : $request->{$method}($url, $payload);
        } catch (ConnectionException $exception) {
            Log::warning('Google Calendar API connection failed.', [
                'exception_class' => get_class($exception),
                'exception_message' => $exception->getMessage(),
            ]);
            throw new GoogleCalendarUnavailable('Google Calendar is unreachable.', 0, $exception);
        }
    }

    private function eventResult(array $event, CarbonImmutable $start, CarbonImmutable $end): array
    {
        if (!isset($event['id'])) {
            throw new GoogleCalendarUnavailable('Google Calendar returned an invalid appointment response.');
        }

        $meetLink = null;
        foreach ($event['conferenceData']['entryPoints'] ?? [] as $entryPoint) {
            if (($entryPoint['entryPointType'] ?? null) === 'video') {
                $meetLink = $entryPoint['uri'] ?? null;
                break;
            }
        }

        return [
            'id' => $event['id'],
            'html_link' => $event['htmlLink'] ?? null,
            'meet_link' => $meetLink,
            'start_at' => $start->toIso8601String(),
            'end_at' => $end->toIso8601String(),
            'timezone' => $this->timezone(),
        ];
    }

    private function ensureSameBooking(array $event, string $bookingKey, CarbonImmutable $start): void
    {
        $existingKey = $event['extendedProperties']['private']['booking_key'] ?? null;
        $existingStart = $event['start']['dateTime'] ?? null;
        if ($existingKey !== $bookingKey
            || !$existingStart
            || !CarbonImmutable::parse($existingStart)->equalTo($start)) {
            throw new AppointmentSlotUnavailable('The appointment request identifier is already in use.');
        }
    }

    private function logCalendarFailure(string $message, int $status): void
    {
        Log::warning($message, ['http_status' => $status]);
    }
}
