@extends('admin.guest')

@section('title', __('admin.sign_in'))

@section('content')
    <h1>{{ __('admin.sign_in') }}</h1>
    <form action="{{ route('admin.login.store') }}" method="post" class="admin-form">
        @csrf
        <label class="form-field">
            <span>{{ __('admin.email') }}</span>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        </label>
        <label class="form-field">
            <span>{{ __('admin.password') }}</span>
            <input type="password" name="password" autocomplete="current-password" required>
        </label>
        <label class="check-field">
            <input type="checkbox" name="remember" value="1">
            <span>{{ __('admin.remember_me') }}</span>
        </label>
        <button class="button" type="submit">{{ __('admin.sign_in_button') }}</button>
    </form>
@endsection
