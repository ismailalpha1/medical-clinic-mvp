<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ClinicLeadNotification;
use App\Models\ClinicSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ClinicSubmissionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'clinic_name' => ['required', 'string', 'max:180'],
            'contact_person' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['required', 'string', 'max:40'],
            'website' => ['nullable', 'url', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'clinic_type' => ['required', 'string', 'max:120'],
            'specialties' => ['nullable', 'array', 'max:20'],
            'specialties.*' => ['string', 'max:80'],
            'services' => ['nullable', 'string', 'max:3000'],
            'number_of_doctors' => ['nullable', 'string', 'max:50'],
            'number_of_locations' => ['nullable', 'string', 'max:50'],
            'year_established' => ['nullable', 'integer', 'between:1800,'.date('Y')],
            'description' => ['nullable', 'string', 'max:3000'],
            'goals' => ['nullable', 'array', 'max:15'],
            'goals.*' => ['string', 'max:120'],
            'preferred_contact_method' => ['required', Rule::in(['Email', 'Phone', 'WhatsApp'])],
            'preferred_language' => ['required', Rule::in(['en', 'es'])],
            'consent' => ['required', 'accepted'],
            'locale' => ['sometimes', 'string', Rule::in(['en', 'es'])],
        ]);

        $locale = $validated['locale'] ?? 'en';
        unset($validated['locale']);
        $submission = ClinicSubmission::create($validated);

        $recipient = config('mail.clinic_lead_notification_email');
        if (!$recipient) {
            Log::warning('Clinic lead notification recipient is not configured.', [
                'submission_id' => $submission->id,
            ]);

            return response()->json([
                'message' => 'The clinic information was saved, but the email notification recipient is not configured.',
            ], 503);
        }

        try {
            Mail::to($recipient)->send(new ClinicLeadNotification($submission, $locale));
        } catch (\Throwable $exception) {
            Log::error('Clinic lead email failed.', [
                'submission_id' => $submission->id,
                'exception' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'The clinic information was saved, but the email notification could not be sent.',
            ], 503);
        }

        return response()->json([
            'message' => 'Thank you. Your clinic information has been received.',
            'id' => $submission->id,
        ], 201);
    }
}
