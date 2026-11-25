<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClinicSubmissionController extends Controller
{
    public function index(?string $status = null): View
    {
        $submissions = ClinicSubmission::query()->latest();
        if ($status !== null) {
            $submissions->where('status', $status);
        }

        return view('admin.clinic-submissions.index', [
            'submissions' => $submissions->paginate(20),
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.clinic-submissions.form', ['submission' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $submission = ClinicSubmission::create($this->normalizeLists($validated));

        return redirect()->route('admin.clinic-submissions.show', $submission)
            ->with('status', __('admin.record_created'));
    }

    public function show(ClinicSubmission $clinicSubmission): View
    {
        return view('admin.clinic-submissions.show', ['submission' => $clinicSubmission]);
    }

    public function edit(ClinicSubmission $clinicSubmission): View
    {
        return view('admin.clinic-submissions.form', ['submission' => $clinicSubmission]);
    }

    public function update(Request $request, ClinicSubmission $clinicSubmission): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $clinicSubmission->update($this->normalizeLists($validated));

        return redirect()->route('admin.clinic-submissions.show', $clinicSubmission)
            ->with('status', __('admin.record_updated'));
    }

    public function updateStatus(Request $request, ClinicSubmission $clinicSubmission): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'accepted', 'refused', 'meeting'])],
        ]);
        $clinicSubmission->update(['status' => $validated['status']]);

        return redirect()->route('admin.clinic-submissions.show', $clinicSubmission)
            ->with('status', __('admin.submission_status_updated'));
    }

    public function destroy(ClinicSubmission $clinicSubmission): RedirectResponse
    {
        $clinicSubmission->delete();

        return redirect()->route('admin.clinic-submissions.index')
            ->with('status', __('admin.record_deleted'));
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
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
            'specialties' => ['nullable', 'string', 'max:1000'],
            'services' => ['nullable', 'string', 'max:3000'],
            'number_of_doctors' => ['nullable', 'string', 'max:50'],
            'number_of_locations' => ['nullable', 'string', 'max:50'],
            'year_established' => ['nullable', 'integer', 'between:1800,'.date('Y')],
            'description' => ['nullable', 'string', 'max:3000'],
            'goals' => ['nullable', 'string', 'max:1000'],
            'preferred_contact_method' => ['required', Rule::in(['Email', 'Phone', 'WhatsApp'])],
            'preferred_language' => ['required', Rule::in(['en', 'es'])],
            'consent' => ['required', 'accepted'],
        ]);
    }

    private function normalizeLists(array $data): array
    {
        foreach (['specialties', 'goals'] as $field) {
            $data[$field] = array_values(array_filter(
                array_map('trim', explode(',', $data[$field] ?? '')),
                fn (string $value) => $value !== '',
            ));
        }

        return $data;
    }
}
