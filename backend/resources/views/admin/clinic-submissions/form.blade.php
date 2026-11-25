@extends('admin.layout')

@section('title', $submission ? __('admin.edit_clinic_submission') : __('admin.new_clinic_submission'))

@section('content')
    <section class="page-heading">
        <div>
            <span class="eyebrow">{{ __('admin.clinic_submissions') }}</span>
            <h1>{{ $submission ? __('admin.edit_clinic_submission') : __('admin.new_clinic_submission') }}</h1>
        </div>
    </section>
    <form action="{{ $submission ? route('admin.clinic-submissions.update', $submission) : route('admin.clinic-submissions.store') }}" method="post" class="admin-form card">
        @csrf
        @if ($submission) @method('put') @endif
        <div class="form-grid">
            @foreach ([
                'clinic_name' => ['type' => 'text', 'required' => true],
                'clinic_type' => ['type' => 'text', 'required' => true],
                'contact_person' => ['type' => 'text', 'required' => true],
                'email' => ['type' => 'email', 'required' => true],
                'phone' => ['type' => 'tel', 'required' => true],
                'website' => ['type' => 'url', 'required' => false],
                'country' => ['type' => 'text', 'required' => true],
                'city' => ['type' => 'text', 'required' => true],
                'postal_code' => ['type' => 'text', 'required' => false],
                'address' => ['type' => 'text', 'required' => false],
                'number_of_doctors' => ['type' => 'text', 'required' => false],
                'number_of_locations' => ['type' => 'text', 'required' => false],
                'year_established' => ['type' => 'number', 'required' => false],
            ] as $field => $settings)
                <label class="form-field">
                    <span>{{ __('admin.'.$field) }}{{ $settings['required'] ? ' *' : '' }}</span>
                    <input type="{{ $settings['type'] }}" name="{{ $field }}" value="{{ old($field, $submission?->{$field} ?? '') }}" @required($settings['required'])>
                    @error($field) <small>{{ __('admin.correct_errors') }}</small> @enderror
                </label>
            @endforeach
            <label class="form-field">
                <span>{{ __('admin.preferred_contact_method') }}</span>
                <select name="preferred_contact_method" required>
                    @foreach (['Email' => __('admin.contact_email'), 'Phone' => __('admin.contact_phone'), 'WhatsApp' => 'WhatsApp'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('preferred_contact_method', $submission?->preferred_contact_method ?? 'Email') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('preferred_contact_method') <small>{{ __('admin.correct_errors') }}</small> @enderror
            </label>
            <label class="form-field">
                <span>{{ __('admin.preferred_language') }}</span>
                <select name="preferred_language" required>
                    <option value="en" @selected(old('preferred_language', $submission?->preferred_language ?? 'en') === 'en')>{{ __('admin.english') }}</option>
                    <option value="es" @selected(old('preferred_language', $submission?->preferred_language ?? 'en') === 'es')>{{ __('admin.spanish') }}</option>
                </select>
                @error('preferred_language') <small>{{ __('admin.correct_errors') }}</small> @enderror
            </label>
            @foreach (['specialties', 'goals'] as $field)
                <label class="form-field form-field-wide">
                    <span>{{ __('admin.'.$field) }}</span>
                    <textarea name="{{ $field }}" rows="2">{{ old($field, implode(', ', $submission?->{$field} ?? [])) }}</textarea>
                    @error($field) <small>{{ __('admin.correct_errors') }}</small> @enderror
                </label>
            @endforeach
            @foreach (['services', 'description'] as $field)
                <label class="form-field form-field-wide">
                    <span>{{ __('admin.'.$field) }}</span>
                    <textarea name="{{ $field }}" rows="3">{{ old($field, $submission?->{$field} ?? '') }}</textarea>
                    @error($field) <small>{{ __('admin.correct_errors') }}</small> @enderror
                </label>
            @endforeach
        </div>
        <label class="check-field">
            <input type="checkbox" name="consent" value="1" @checked(old('consent', $submission?->consent ?? false)) required>
            <span>{{ __('admin.consent') }}</span>
        </label>
        @error('consent') <small>{{ __('admin.correct_errors') }}</small> @enderror
        <div class="form-actions">
            <a class="button button-muted" href="{{ $submission ? route('admin.clinic-submissions.show', $submission) : route('admin.clinic-submissions.index') }}">{{ __('admin.cancel') }}</a>
            <button class="button" type="submit">{{ __('admin.save') }}</button>
        </div>
    </form>
@endsection
