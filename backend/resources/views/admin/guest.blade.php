<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title') | {{ __('admin.brand') }}</title>
    <link rel="stylesheet" href="{{ asset('admin.css') }}">
</head>
<body class="guest-page">
    <main class="guest-panel">
        <div class="guest-header">
            <a class="admin-brand" href="{{ route('admin.login') }}">{{ __('admin.brand') }}</a>
            <form action="{{ route('admin.language') }}" method="post" class="language-form">
                @csrf
                <label class="sr-only" for="admin-locale">{{ __('admin.language') }}</label>
                <select id="admin-locale" name="locale">
                    <option value="en" @selected(app()->getLocale() === 'en')>{{ __('admin.english') }}</option>
                    <option value="es" @selected(app()->getLocale() === 'es')>{{ __('admin.spanish') }}</option>
                </select>
                <button class="button button-muted" type="submit">{{ __('admin.language') }}</button>
            </form>
        </div>
        @if ($errors->any())
            <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
