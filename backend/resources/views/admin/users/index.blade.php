@extends('admin.layout')

@section('title', __('admin.team_members'))

@section('content')
    <section class="page-heading">
        <div>
            <span class="eyebrow">{{ __('admin.records') }}</span>
            <h1>{{ __('admin.team_members') }}</h1>
        </div>
        <a class="button" href="{{ route('admin.users.create') }}">{{ __('admin.new_admin_account') }}</a>
    </section>

    @if (session('temporary_credentials'))
        <section class="temporary-credentials" aria-live="polite">
            <h2>{{ __('admin.temporary_credentials') }}</h2>
            <p><strong>{{ __('admin.email') }}:</strong> {{ session('temporary_credentials.email') }}</p>
            <p><strong>{{ __('admin.temporary_password') }}:</strong> <code>{{ session('temporary_credentials.password') }}</code></p>
            <p>{{ __('admin.copy_and_share') }}</p>
        </section>
    @endif

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>{{ __('admin.name') }}</th>
                    <th>{{ __('admin.email') }}</th>
                    <th>{{ __('admin.role') }}</th>
                    <th>{{ __('admin.account_status') }}</th>
                    <th>{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $adminUser)
                    @php($roleKey = $adminUser->hasRole('super-admin') ? 'role_super_admin' : 'role_admin')
                    <tr>
                        <td data-label="{{ __('admin.name') }}">{{ $adminUser->name }}</td>
                        <td data-label="{{ __('admin.email') }}">{{ $adminUser->email }}</td>
                        <td data-label="{{ __('admin.role') }}">{{ __('admin.'.$roleKey) }}</td>
                        <td data-label="{{ __('admin.account_status') }}">
                            <span class="status-pill {{ $adminUser->is_active ? 'status-active' : 'status-disabled' }}">
                                {{ $adminUser->is_active ? __('admin.active') : __('admin.disabled') }}
                            </span>
                        </td>
                        <td data-label="{{ __('admin.actions') }}"><a class="text-action" href="{{ route('admin.users.edit', $adminUser) }}">{{ __('admin.edit') }}</a></td>
                    </tr>
                @empty
                    <tr><td class="empty-state" colspan="5">{{ __('admin.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())
        <nav class="pagination" aria-label="{{ __('admin.records') }}">
            @if ($users->previousPageUrl()) <a href="{{ $users->previousPageUrl() }}">{{ __('admin.previous') }}</a> @endif
            <span>{{ __('admin.page', ['current' => $users->currentPage(), 'last' => $users->lastPage()]) }}</span>
            @if ($users->nextPageUrl()) <a href="{{ $users->nextPageUrl() }}">{{ __('admin.next') }}</a> @endif
        </nav>
    @endif
@endsection
