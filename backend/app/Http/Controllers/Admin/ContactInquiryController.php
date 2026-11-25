<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactInquiryController extends Controller
{
    public function index(): View
    {
        return view('admin.contact-inquiries.index', [
            'inquiries' => ContactInquiry::latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.contact-inquiries.form', ['inquiry' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $inquiry = ContactInquiry::create($this->validatedData($request));

        return redirect()->route('admin.contact-inquiries.show', $inquiry)
            ->with('status', __('admin.record_created'));
    }

    public function show(ContactInquiry $contactInquiry): View
    {
        return view('admin.contact-inquiries.show', ['inquiry' => $contactInquiry]);
    }

    public function edit(ContactInquiry $contactInquiry): View
    {
        return view('admin.contact-inquiries.form', ['inquiry' => $contactInquiry]);
    }

    public function update(Request $request, ContactInquiry $contactInquiry): RedirectResponse
    {
        $contactInquiry->update($this->validatedData($request));

        return redirect()->route('admin.contact-inquiries.show', $contactInquiry)
            ->with('status', __('admin.record_updated'));
    }

    public function destroy(ContactInquiry $contactInquiry): RedirectResponse
    {
        $contactInquiry->delete();

        return redirect()->route('admin.contact-inquiries.index')
            ->with('status', __('admin.record_deleted'));
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
            'visit_reason' => ['nullable', 'string', 'max:300'],
            'consent' => ['required', 'accepted'],
        ]);
    }
}
