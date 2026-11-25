@extends('admin.layout')

@section('title', __('admin.contact_inquiries'))

@section('content')
    <section class="page-heading">
        <div>
            <span class="eyebrow">{{ __('admin.records') }}</span>
            <h1>{{ __('admin.contact_inquiries') }}</h1>
        </div>
        <div class="heading-actions">
            <a class="button button-muted" href="{{ route('admin.contact-inquiries.export') }}">{{ __('admin.export_excel') }}</a>
            @can('contact-inquiries.create')
                <a class="button" href="{{ route('admin.contact-inquiries.create') }}">{{ __('admin.add') }}</a>
            @endcan
        </div>
    </section>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>{{ __('admin.name') }}</th>
                    <th>{{ __('admin.subject') }}</th>
                    <th>{{ __('admin.email') }}</th>
                    <th>{{ __('admin.created_at') }}</th>
                    <th>{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inquiries as $inquiry)
                    <tr>
                        <td data-label="{{ __('admin.name') }}">{{ $inquiry->name }}</td>
                        <td data-label="{{ __('admin.subject') }}">{{ $inquiry->subject }}</td>
                        <td data-label="{{ __('admin.email') }}">{{ $inquiry->email }}</td>
                        <td data-label="{{ __('admin.created_at') }}">{{ $inquiry->created_at->format('Y-m-d') }}</td>
                        <td data-label="{{ __('admin.actions') }}"><a class="text-action" href="{{ route('admin.contact-inquiries.show', $inquiry) }}">{{ __('admin.view') }}</a></td>
                    </tr>
                @empty
                    <tr><td class="empty-state" colspan="5">{{ __('admin.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($inquiries->hasPages())
        <nav class="pagination" aria-label="{{ __('admin.records') }}">
            @if ($inquiries->previousPageUrl()) <a href="{{ $inquiries->previousPageUrl() }}">{{ __('admin.previous') }}</a> @endif
            <span>{{ __('admin.page', ['current' => $inquiries->currentPage(), 'last' => $inquiries->lastPage()]) }}</span>
            @if ($inquiries->nextPageUrl()) <a href="{{ $inquiries->nextPageUrl() }}">{{ __('admin.next') }}</a> @endif
        </nav>
    @endif
@endsection
