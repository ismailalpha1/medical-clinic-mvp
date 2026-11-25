@extends('admin.layout')

@section('title', __('admin.contact_inquiry_details'))

@section('content')
    <section class="page-heading">
        <div>
            <span class="eyebrow">{{ __('admin.contact_inquiries') }}</span>
            <h1>{{ $inquiry->subject }}</h1>
        </div>
        <div class="heading-actions">
            @can('contact-inquiries.update')
                <a class="button button-muted" href="{{ route('admin.contact-inquiries.edit', $inquiry) }}">{{ __('admin.edit') }}</a>
            @endcan
            @can('contact-inquiries.delete')
                <form action="{{ route('admin.contact-inquiries.destroy', $inquiry) }}" method="post" onsubmit="return confirm(@js(__('admin.delete_confirm')))">
                    @csrf
                    @method('delete')
                    <button class="button button-danger" type="submit">{{ __('admin.delete') }}</button>
                </form>
            @endcan
        </div>
    </section>
    <section class="card">
        <h2>{{ __('admin.contact_inquiry_details') }}</h2>
        <dl class="details-grid">
            @foreach ([
                'name' => $inquiry->name,
                'email' => $inquiry->email,
                'phone' => $inquiry->phone,
                'subject' => $inquiry->subject,
                'message' => $inquiry->message,
                'visit_reason' => $inquiry->visit_reason,
                'consent' => $inquiry->consent ? __('admin.yes') : __('admin.no'),
                'created_at' => $inquiry->created_at->format('Y-m-d H:i'),
            ] as $field => $value)
                <div class="{{ in_array($field, ['message', 'visit_reason'], true) ? 'detail-wide' : '' }}">
                    <dt>{{ __('admin.'.$field) }}</dt>
                    <dd class="preserve-lines">{{ $value ?: __('admin.not_provided') }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
    <a class="text-action back-link" href="{{ route('admin.contact-inquiries.index') }}">← {{ __('admin.back_to_list') }}</a>
@endsection
