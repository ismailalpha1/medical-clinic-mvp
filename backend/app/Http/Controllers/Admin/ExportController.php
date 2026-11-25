<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicSubmission;
use App\Models\ContactInquiry;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function clinicSubmissions(): StreamedResponse
    {
        return $this->download(
            'clinic-submissions',
            [
                __('admin.clinic_name'),
                __('admin.clinic_type'),
                __('admin.contact_person'),
                __('admin.email'),
                __('admin.phone'),
                __('admin.website'),
                __('admin.country'),
                __('admin.city'),
                __('admin.postal_code'),
                __('admin.address'),
                __('admin.people_working'),
                __('admin.number_of_locations'),
                __('admin.year_established'),
                __('admin.specialties'),
                __('admin.services'),
                __('admin.description'),
                __('admin.goals'),
                __('admin.preferred_contact_method'),
                __('admin.preferred_language'),
                __('admin.status'),
                __('admin.consent'),
                __('admin.created_at'),
            ],
            ClinicSubmission::query()->orderBy('id')->cursor()->map(fn (ClinicSubmission $submission) => [
                $submission->clinic_name,
                $submission->clinic_type,
                $submission->contact_person,
                $submission->email,
                $submission->phone,
                $submission->website,
                $submission->country,
                $submission->city,
                $submission->postal_code,
                $submission->address,
                $submission->number_of_doctors,
                $submission->number_of_locations,
                $submission->year_established,
                implode(', ', $submission->specialties ?? []),
                $submission->services,
                $submission->description,
                implode(', ', $submission->goals ?? []),
                $submission->preferred_contact_method,
                $submission->preferred_language,
                $submission->status,
                $submission->consent ? __('admin.yes') : __('admin.no'),
                $submission->created_at?->toDateTimeString(),
            ]),
        );
    }

    public function contactInquiries(): StreamedResponse
    {
        return $this->download(
            'contact-inquiries',
            [
                __('admin.name'),
                __('admin.email'),
                __('admin.phone'),
                __('admin.subject'),
                __('admin.message'),
                __('admin.visit_reason'),
                __('admin.consent'),
                __('admin.created_at'),
            ],
            ContactInquiry::query()->orderBy('id')->cursor()->map(fn (ContactInquiry $inquiry) => [
                $inquiry->name,
                $inquiry->email,
                $inquiry->phone,
                $inquiry->subject,
                $inquiry->message,
                $inquiry->visit_reason,
                $inquiry->consent ? __('admin.yes') : __('admin.no'),
                $inquiry->created_at?->toDateTimeString(),
            ]),
        );
    }

    private function download(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers, ',', '"', '');

            foreach ($rows as $row) {
                fputcsv($output, array_map($this->spreadsheetSafe(...), $row), ',', '"', '');
            }

            fclose($output);
        }, $filename.'-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function spreadsheetSafe(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^\s*[=+\-@\t\r]/u', $value) === 1 ? "'".$value : $value;
    }
}
