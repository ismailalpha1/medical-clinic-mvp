<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactInquiryNotification;
use App\Models\ContactInquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ContactInquiryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required_without:phone', 'nullable', 'email', 'max:180'],
            'phone' => ['required_without:email', 'nullable', 'string', 'max:40'],
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
            'visit_reason' => ['nullable', 'string', 'max:300'],
            'consent' => ['required', 'accepted'],
            'locale' => ['sometimes', 'string', Rule::in(['en', 'es'])],
        ]);

        $locale = $validated['locale'] ?? 'en';
        unset($validated['locale']);
        $inquiry = ContactInquiry::create($validated);

        try {
            $recipient = config('mail.clinic_lead_notification_email');
            if ($recipient) {
                Mail::to($recipient)->send(new ContactInquiryNotification($inquiry, $locale));
            }
        } catch (\Throwable $exception) {
            Log::error('Contact inquiry email failed.', [
                'inquiry_id' => $inquiry->id,
                'exception' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Thank you. Your message has been received.',
            'id' => $inquiry->id,
        ], 201);
    }
}
