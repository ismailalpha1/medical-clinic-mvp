@extends('admin.layout')

@section('title', __('admin.clinic_submission_details'))

@section('content')
    <section class="page-heading">
        <div>
            <span class="eyebrow">{{ __('admin.clinic_submissions') }}</span>
            <h1>{{ $submission->clinic_name }}</h1>
        </div>
        <div class="heading-actions">
            @can('clinic-submissions.update')
                <a class="button button-muted" href="{{ route('admin.clinic-submissions.edit', $submission) }}">{{ __('admin.edit') }}</a>
            @endcan
            @can('clinic-submissions.delete')
                <form action="{{ route('admin.clinic-submissions.destroy', $submission) }}" method="post" onsubmit="return confirm(@js(__('admin.delete_confirm')))">
                    @csrf
                    @method('delete')
                    <button class="button button-danger" type="submit">{{ __('admin.delete') }}</button>
                </form>
            @endcan
        </div>
    </section>
    <section class="card status-card">
        <div class="status-card-heading">
            <div>
                <h2>{{ __('admin.status') }}</h2>
                <span class="status-pill status-{{ $submission->status }}">{{ __('admin.status_'.$submission->status) }}</span>
            </div>
            @role('super-admin')
                <form action="{{ route('admin.clinic-submissions.status', $submission) }}" method="post" class="status-update-form">
                    @csrf
                    @method('patch')
                    <label class="form-field">
                        <span>{{ __('admin.change_status') }}</span>
                        <select name="status" required>
                            @foreach (['pending', 'accepted', 'refused', 'meeting'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $submission->status) === $status)>{{ __('admin.status_'.$status) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button class="button" type="submit">{{ __('admin.save_status') }}</button>
                </form>
            @endrole
        </div>
    </section>
    <section class="card">
        <h2>{{ __('admin.clinic_submission_details') }}</h2>
        <dl class="details-grid">
            @foreach ([
                'clinic_type' => $submission->clinic_type,
                'contact_person' => $submission->contact_person,
                'email' => $submission->email,
                'phone' => $submission->phone,
                'website' => $submission->website,
                'country' => $submission->country,
                'city' => $submission->city,
                'postal_code' => $submission->postal_code,
                'address' => $submission->address,
                'number_of_doctors' => $submission->number_of_doctors,
                'number_of_locations' => $submission->number_of_locations,
                'year_established' => $submission->year_established,
                'specialties' => implode(', ', $submission->specialties ?? []),
                'services' => $submission->services,
                'description' => $submission->description,
                'goals' => implode(', ', $submission->goals ?? []),
                'preferred_contact_method' => match ($submission->preferred_contact_method) {
                    'Phone' => __('admin.contact_phone'),
                    'WhatsApp' => __('admin.contact_whatsapp'),
                    default => __('admin.contact_email'),
                },
                'preferred_language' => $submission->preferred_language === 'es' ? __('admin.spanish') : __('admin.english'),
                'status' => __('admin.status_'.$submission->status),
                'consent' => $submission->consent ? __('admin.yes') : __('admin.no'),
                'created_at' => $submission->created_at->format('Y-m-d H:i'),
            ] as $field => $value)
                <div>
                    <dt>{{ __('admin.'.$field) }}</dt>
                    <dd>{{ $value ?: __('admin.not_provided') }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
    <a class="text-action back-link" href="{{ route('admin.clinic-submissions.index') }}">← {{ __('admin.back_to_list') }}</a>
@endsection
