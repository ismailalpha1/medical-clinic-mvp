@extends('admin.layout')

@section('title', $status ? __('admin.status_submissions', ['status' => __('admin.status_'.$status)]) : __('admin.clinic_submissions'))

@section('content')
    <section class="page-heading">
        <div>
            <span class="eyebrow">{{ __('admin.records') }}</span>
            <h1>{{ $status ? __('admin.status_submissions', ['status' => __('admin.status_'.$status)]) : __('admin.clinic_submissions') }}</h1>
        </div>
        <div class="heading-actions">
            <a class="button button-muted" href="{{ route('admin.clinic-submissions.export') }}">{{ __('admin.export_excel') }}</a>
            @can('clinic-submissions.create')
                <a class="button" href="{{ route('admin.clinic-submissions.create') }}">{{ __('admin.add') }}</a>
            @endcan
        </div>
    </section>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>{{ __('admin.clinic_name') }}</th>
                    <th>{{ __('admin.country') }} / {{ __('admin.city') }}</th>
                    <th>{{ __('admin.contact_person') }}</th>
                    <th>{{ __('admin.status') }}</th>
                    <th>{{ __('admin.created_at') }}</th>
                    <th>{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($submissions as $submission)
                    <tr>
                        <td data-label="{{ __('admin.clinic_name') }}">{{ $submission->clinic_name }}</td>
                        <td data-label="{{ __('admin.country') }}">{{ $submission->city }}, {{ $submission->country }}</td>
                        <td data-label="{{ __('admin.contact_person') }}">{{ $submission->contact_person }}</td>
                        <td data-label="{{ __('admin.status') }}"><span class="status-pill status-{{ $submission->status }}">{{ __('admin.status_'.$submission->status) }}</span></td>
                        <td data-label="{{ __('admin.created_at') }}">{{ $submission->created_at->format('Y-m-d') }}</td>
                        <td data-label="{{ __('admin.actions') }}"><a class="text-action" href="{{ route('admin.clinic-submissions.show', $submission) }}">{{ __('admin.view') }}</a></td>
                    </tr>
                @empty
                    <tr><td class="empty-state" colspan="6">{{ __('admin.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($submissions->hasPages())
        <nav class="pagination" aria-label="{{ __('admin.records') }}">
            @if ($submissions->previousPageUrl()) <a href="{{ $submissions->previousPageUrl() }}">{{ __('admin.previous') }}</a> @endif
            <span>{{ __('admin.page', ['current' => $submissions->currentPage(), 'last' => $submissions->lastPage()]) }}</span>
            @if ($submissions->nextPageUrl()) <a href="{{ $submissions->nextPageUrl() }}">{{ __('admin.next') }}</a> @endif
        </nav>
    @endif
@endsection
