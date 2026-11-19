<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AppointmentSlotUnavailable;
use App\Exceptions\GoogleCalendarUnavailable;
use App\Http\Controllers\Controller;
use App\Mail\ContactInquiryNotification;
use App\Models\ContactInquiry;
use App\Services\GoogleCalendarService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public function availability(Request $request, GoogleCalendarService $calendar): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'need' => ['required', 'string', Rule::in(['appointment', 'services', 'location', 'callback', 'other'])],
        ]);

        try {
            return response()->json($calendar->availability($validated['date'], $validated['need']));
        } catch (GoogleCalendarUnavailable $exception) {
            Log::warning('Google Calendar availability is unavailable.', ['reason' => $exception->getMessage()]);

            return response()->json(['code' => 'calendar_unavailable'], 503);
        }
    }

    public function store(Request $request, GoogleCalendarService $calendar): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required_without:phone', 'nullable', 'email', 'max:180'],
            'phone' => ['required_without:email', 'nullable', 'string', 'max:40'],
            'need' => ['required', 'string', Rule::in(['appointment', 'services', 'location', 'callback', 'other'])],
            'meeting_type' => ['required', 'string', Rule::in(['clinic', 'google_meet'])],
            'location' => ['required_if:meeting_type,clinic', 'nullable', 'string', 'max:180'],
            'start_at' => ['required', 'date'],
            'idempotency_key' => ['required', 'uuid'],
            'visit_reason' => ['nullable', 'string', 'max:300'],
            'consent' => ['required', 'accepted'],
            'locale' => ['sometimes', 'string', Rule::in(['en', 'es'])],
        ]);

        $validated['email'] = $validated['email'] ?? null;
        $validated['phone'] = $validated['phone'] ?? null;
        $validated['location'] = $validated['location'] ?? null;
        $validated['visit_reason'] = $validated['visit_reason'] ?? null;

        if ($validated['meeting_type'] === 'google_meet' && !$validated['email']) {
            return response()->json(['code' => 'email_required_for_video'], 422);
        }

        $locale = $validated['locale'] ?? 'en';

        try {
            $event = $calendar->book($validated);
        } catch (AppointmentSlotUnavailable $exception) {
            return response()->json(['code' => 'slot_unavailable'], 409);
        } catch (GoogleCalendarUnavailable $exception) {
            Log::error('Google Calendar booking failed.', ['reason' => $exception->getMessage()]);

            return response()->json(['code' => 'calendar_unavailable'], 503);
        }

        $isSpanish = $locale === 'es';
        $needLabels = [
            'appointment' => $isSpanish ? 'Solicitud de cita' : 'Appointment request',
            'services' => $isSpanish ? 'Consulta sobre servicios' : 'Services inquiry',
            'location' => $isSpanish ? 'Consulta sobre ubicación' : 'Location inquiry',
            'callback' => $isSpanish ? 'Solicitud de llamada' : 'Call-back request',
            'other' => $isSpanish ? 'Otra consulta' : 'Other inquiry',
        ];
        $meetingLabel = $validated['meeting_type'] === 'google_meet'
            ? 'Google Meet'
            : ($isSpanish ? 'Presencial' : 'In person');
        $localStart = CarbonImmutable::parse($event['start_at'])->setTimezone($event['timezone']);
        $localEnd = CarbonImmutable::parse($event['end_at'])->setTimezone($event['timezone']);
        $messageLines = [
            ($isSpanish ? 'Tipo de consulta: ' : 'Request type: ').$needLabels[$validated['need']],
            ($isSpanish ? 'Fecha y hora: ' : 'Appointment time: ').$localStart->format('Y-m-d H:i T')
                .' - '.$localEnd->format('H:i T'),
            ($isSpanish ? 'Modalidad: ' : 'Meeting type: ').$meetingLabel,
        ];
        if ($validated['meeting_type'] === 'clinic') {
            $messageLines[] = ($isSpanish ? 'Ubicación: ' : 'Location: ').$validated['location'];
        }

        $inquiry = ContactInquiry::firstOrCreate(
            ['calendar_event_id' => $event['id']],
            [
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'subject' => $isSpanish ? 'Cita solicitada desde el chat' : 'Appointment booked from website chat',
                'message' => implode("\n", $messageLines),
                'visit_reason' => $validated['visit_reason'] ?? null,
                'consent' => true,
            ]
        );

        if ($inquiry->wasRecentlyCreated) {
            try {
                $recipient = config('mail.clinic_lead_notification_email');
                if ($recipient) {
                    Mail::to($recipient)->send(new ContactInquiryNotification($inquiry, $locale));
                }
            } catch (\Throwable $exception) {
                Log::error('Appointment inquiry email failed.', [
                    'inquiry_id' => $inquiry->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json([
            'message' => $isSpanish ? 'La cita quedó reservada.' : 'The appointment has been booked.',
            'appointment' => $event,
        ], 201);
    }
}
