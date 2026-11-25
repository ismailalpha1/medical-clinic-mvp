<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title') | {{ __('admin.brand') }}</title>
    <script>
        const savedTheme = localStorage.getItem('admin-theme');
        document.documentElement.dataset.theme = savedTheme === 'dark' || savedTheme === 'light'
            ? savedTheme
            : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    </script>
    <link rel="stylesheet" href="{{ asset('admin.css') }}">
</head>
<body>
    <div class="admin-shell">
        <input class="sidebar-toggle-control" type="checkbox" id="admin-sidebar-toggle">
        <aside class="admin-sidebar">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}">{{ __('admin.brand') }}</a>
            <nav class="admin-nav" aria-label="{{ __('admin.dashboard') }}">
                <a @class(['active' => request()->routeIs('admin.dashboard')]) href="{{ route('admin.dashboard') }}">{{ __('admin.dashboard') }}</a>
                @can('clinic-submissions.view')
                    <details class="sidebar-group" @if (request()->routeIs('admin.clinic-submissions.*')) open @endif>
                        <summary>{{ __('admin.clinic_submissions') }}</summary>
                        <div class="sidebar-subnav">
                            <a @class(['active' => request()->routeIs('admin.clinic-submissions.index')]) href="{{ route('admin.clinic-submissions.index') }}">{{ __('admin.all_submissions') }}</a>
                            @foreach (['pending', 'accepted', 'refused', 'meeting'] as $submissionStatus)
                                <a @class(['active' => request()->route('status') === $submissionStatus]) href="{{ route('admin.clinic-submissions.status-list', $submissionStatus) }}">
                                    {{ __('admin.status_'.$submissionStatus) }}
                                </a>
                            @endforeach
                        </div>
                    </details>
                @endcan
                @can('contact-inquiries.view')
                    <a @class(['active' => request()->routeIs('admin.contact-inquiries.*')]) href="{{ route('admin.contact-inquiries.index') }}">{{ __('admin.contact_inquiries') }}</a>
                @endcan
                @can('admin-users.manage')
                    <a @class(['active' => request()->routeIs('admin.users.*')]) href="{{ route('admin.users.index') }}">{{ __('admin.team_members') }}</a>
                @endcan
            </nav>
        </aside>
        <label class="sidebar-backdrop" for="admin-sidebar-toggle" aria-hidden="true"></label>
        <div class="admin-content">
            <header class="admin-topbar">
                <label class="sidebar-toggle" for="admin-sidebar-toggle">
                    <span aria-hidden="true">☰</span>
                    <span class="sr-only">{{ __('admin.menu') }}</span>
                </label>
                <div class="admin-account">
                    <button class="button button-muted theme-toggle" type="button"
                        data-theme-toggle
                        data-dark-label="{{ __('admin.dark_mode') }}"
                        data-light-label="{{ __('admin.light_mode') }}"
                        aria-pressed="false">
                        <span class="theme-icon" aria-hidden="true">◐</span>
                        <span data-theme-label>{{ __('admin.dark_mode') }}</span>
                    </button>
                    <form action="{{ route('admin.language') }}" method="post" class="language-form">
                        @csrf
                        <label class="sr-only" for="admin-locale">{{ __('admin.language') }}</label>
                        <select id="admin-locale" name="locale">
                            <option value="en" @selected(app()->getLocale() === 'en')>{{ __('admin.english') }}</option>
                            <option value="es" @selected(app()->getLocale() === 'es')>{{ __('admin.spanish') }}</option>
                        </select>
                        <button class="button button-muted" type="submit">{{ __('admin.language') }}</button>
                    </form>
                    <span class="admin-user-name">{{ auth()->user()->name }}</span>
                    <form action="{{ route('admin.logout') }}" method="post">
                        @csrf
                        <button class="button button-muted" type="submit">{{ __('admin.logout') }}</button>
                    </form>
                </div>
            </header>
            <main class="admin-main">
                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-error" role="alert">{{ __('admin.correct_errors') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    <script src="{{ asset('admin.js') }}"></script>
</body>
</html>
