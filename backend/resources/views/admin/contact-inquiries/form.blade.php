@extends('admin.layout')

@section('title', $inquiry ? __('admin.edit_contact_inquiry') : __('admin.new_contact_inquiry'))

@section('content')
    <section class="page-heading">
        <div>
            <span class="eyebrow">{{ __('admin.contact_inquiries') }}</span>
            <h1>{{ $inquiry ? __('admin.edit_contact_inquiry') : __('admin.new_contact_inquiry') }}</h1>
        </div>
    </section>
    <form action="{{ $inquiry ? route('admin.contact-inquiries.update', $inquiry) : route('admin.contact-inquiries.store') }}" method="post" class="admin-form card">
        @csrf
        @if ($inquiry) @method('put') @endif
        <div class="form-grid">
            @foreach (['name' => 'text', 'email' => 'email', 'phone' => 'tel', 'subject' => 'text'] as $field => $type)
                <label class="form-field">
                    <span>{{ __('admin.'.$field) }}{{ $field !== 'phone' ? ' *' : '' }}</span>
                    <input type="{{ $type }}" name="{{ $field }}" value="{{ old($field, $inquiry?->{$field} ?? '') }}" @required($field !== 'phone')>
                    @error($field) <small>{{ __('admin.correct_errors') }}</small> @enderror
                </label>
            @endforeach
            <label class="form-field form-field-wide">
                <span>{{ __('admin.message') }} *</span>
                <textarea name="message" rows="6" required>{{ old('message', $inquiry?->message ?? '') }}</textarea>
                @error('message') <small>{{ __('admin.correct_errors') }}</small> @enderror
            </label>
            <label class="form-field form-field-wide">
                <span>{{ __('admin.visit_reason') }}</span>
                <textarea name="visit_reason" rows="2">{{ old('visit_reason', $inquiry?->visit_reason ?? '') }}</textarea>
                @error('visit_reason') <small>{{ __('admin.correct_errors') }}</small> @enderror
            </label>
        </div>
        <label class="check-field">
            <input type="checkbox" name="consent" value="1" @checked(old('consent', $inquiry?->consent ?? false)) required>
            <span>{{ __('admin.consent') }}</span>
        </label>
        @error('consent') <small>{{ __('admin.correct_errors') }}</small> @enderror
        <div class="form-actions">
            <a class="button button-muted" href="{{ $inquiry ? route('admin.contact-inquiries.show', $inquiry) : route('admin.contact-inquiries.index') }}">{{ __('admin.cancel') }}</a>
            <button class="button" type="submit">{{ __('admin.save') }}</button>
        </div>
    </form>
@endsection
