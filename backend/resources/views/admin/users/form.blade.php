@extends('admin.layout')

@section('title', $adminUser ? __('admin.edit_admin_account') : __('admin.new_admin_account'))

@section('content')
    <section class="page-heading">
        <div>
            <span class="eyebrow">{{ __('admin.team_members') }}</span>
            <h1>{{ $adminUser ? __('admin.edit_admin_account') : __('admin.new_admin_account') }}</h1>
        </div>
    </section>
    <form action="{{ $adminUser ? route('admin.users.update', $adminUser) : route('admin.users.store') }}" method="post" class="admin-form card">
        @csrf
        @if ($adminUser) @method('put') @endif
        <label class="form-field">
            <span>{{ __('admin.name') }}</span>
            <input type="text" name="name" value="{{ old('name', $adminUser?->name ?? '') }}" required>
            @error('name') <small>{{ __('admin.correct_errors') }}</small> @enderror
        </label>
        <label class="form-field">
            <span>{{ __('admin.email') }}</span>
            <input type="email" name="email" value="{{ old('email', $adminUser?->email ?? '') }}" required>
            @error('email') <small>{{ __('admin.correct_errors') }}</small> @enderror
        </label>
        <label class="form-field">
            <span>{{ __('admin.role') }}</span>
            <select name="role" required>
                <option value="admin" @selected(old('role', $adminUser?->hasRole('super-admin') ? 'super-admin' : 'admin') === 'admin')>{{ __('admin.role_admin') }}</option>
                <option value="super-admin" @selected(old('role', $adminUser?->hasRole('super-admin') ? 'super-admin' : 'admin') === 'super-admin')>{{ __('admin.role_super_admin') }}</option>
            </select>
            @error('role') <small>{{ __('admin.correct_errors') }}</small> @enderror
        </label>
        @if ($adminUser)
            <label class="form-field">
                <span>{{ __('admin.account_status') }}</span>
                <select name="is_active" required>
                    <option value="1" @selected((string) old('is_active', (int) $adminUser->is_active) === '1')>{{ __('admin.active') }}</option>
                    <option value="0" @selected((string) old('is_active', (int) $adminUser->is_active) === '0')>{{ __('admin.disabled') }}</option>
                </select>
                @error('is_active') <small>{{ __('admin.correct_errors') }}</small> @enderror
            </label>
        @endif
        <div class="form-actions">
            <a class="button button-muted" href="{{ route('admin.users.index') }}">{{ __('admin.cancel') }}</a>
            <button class="button" type="submit">{{ $adminUser ? __('admin.save') : __('admin.create_account') }}</button>
        </div>
    </form>

    @if ($adminUser)
        <form action="{{ route('admin.users.reset-password', $adminUser) }}" method="post" class="card reset-password-form" onsubmit="return confirm(@js(__('admin.reset_password_confirm')))">
            @csrf
            <h2>{{ __('admin.change_password') }}</h2>
            <p>{{ __('admin.copy_and_share') }}</p>
            <button class="button button-muted" type="submit">{{ __('admin.reset_password') }}</button>
        </form>
    @endif
@endsection
