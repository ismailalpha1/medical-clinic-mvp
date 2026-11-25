@extends('admin.layout')

@section('title', __('admin.change_password'))

@section('content')
    <section class="page-heading">
        <div>
            <span class="eyebrow">{{ __('admin.brand') }}</span>
            <h1>{{ __('admin.change_password') }}</h1>
            @if (auth()->user()->must_change_password)
                <p>{{ __('admin.password_change_required') }}</p>
            @endif
        </div>
    </section>
    <form action="{{ route('admin.password.update') }}" method="post" class="admin-form card">
        @csrf
        @method('put')
        <label class="form-field">
            <span>{{ __('admin.current_password') }}</span>
            <input type="password" name="current_password" autocomplete="current-password" required>
            @error('current_password') <small>{{ __('admin.correct_errors') }}</small> @enderror
        </label>
        <label class="form-field">
            <span>{{ __('admin.new_password') }}</span>
            <input type="password" name="password" autocomplete="new-password" minlength="12" required>
            <small>{{ __('admin.password_rule') }}</small>
            @error('password') <small>{{ __('admin.correct_errors') }}</small> @enderror
        </label>
        <label class="form-field">
            <span>{{ __('admin.confirm_password') }}</span>
            <input type="password" name="password_confirmation" autocomplete="new-password" minlength="12" required>
        </label>
        <div class="form-actions">
            <button class="button" type="submit">{{ __('admin.update_password') }}</button>
        </div>
    </form>
@endsection
