@extends('admin.layout')

@section('title', __('admin.dashboard'))

@section('content')
    <section class="page-heading">
        <div>
            <span class="eyebrow">{{ __('admin.brand') }}</span>
            <h1>{{ __('admin.welcome', ['name' => auth()->user()->name]) }}</h1>
            <p>{{ __('admin.analytics_description') }}</p>
        </div>
    </section>
    <section class="dashboard-grid">
        @can('clinic-submissions.view')
            <a class="dashboard-card" href="{{ route('admin.clinic-submissions.index') }}">
                <span>{{ __('admin.total_clinic_submissions') }}</span>
                <strong>{{ number_format($clinicSubmissionCount) }}</strong>
                <small>{{ __('admin.recent_submissions') }}</small>
            </a>
        @endcan
        @can('contact-inquiries.view')
            <a class="dashboard-card" href="{{ route('admin.contact-inquiries.index') }}">
                <span>{{ __('admin.total_contact_inquiries') }}</span>
                <strong>{{ number_format($contactInquiryCount) }}</strong>
                <small>{{ __('admin.recent_inquiries') }}</small>
            </a>
        @endcan
        @can('admin-users.manage')
            <a class="dashboard-card" href="{{ route('admin.users.index') }}">
                <span>{{ __('admin.admin_accounts') }}</span>
                <strong>{{ number_format($adminAccountCount) }}</strong>
                <small>{{ __('admin.manage_admin_accounts') }}</small>
            </a>
        @endcan
        <article class="dashboard-card">
            <span>{{ __('admin.average_submissions_per_month') }}</span>
            <strong>{{ number_format($averageSubmissionsPerMonth, 1) }}</strong>
            <small>{{ __('admin.last_six_months') }}</small>
        </article>
        <article class="dashboard-card">
            <span>{{ __('admin.average_inquiries_per_month') }}</span>
            <strong>{{ number_format($averageInquiriesPerMonth, 1) }}</strong>
            <small>{{ __('admin.last_six_months') }}</small>
        </article>
    </section>

    <section class="analytics-grid">
        <article class="card analytics-chart-card">
            <div class="analytics-heading">
                <div>
                    <h2>{{ __('admin.monthly_trend') }}</h2>
                    <p>{{ __('admin.last_six_months') }}</p>
                </div>
                <div class="chart-legend">
                    <span><i class="legend-dot legend-submissions"></i>{{ __('admin.clinic_submissions') }}</span>
                    <span><i class="legend-dot legend-inquiries"></i>{{ __('admin.contact_inquiries') }}</span>
                </div>
            </div>
            @php
                $chartMax = max(1, (int) $monthlyTrend->flatMap(fn ($month) => [$month['submissions'], $month['inquiries']])->max());
                $chartPoints = $monthlyTrend->values()->map(function ($month, $index) use ($chartMax) {
                    return [
                        'x' => 28 + ($index * 108),
                        'submissionY' => 145 - ($month['submissions'] / $chartMax * 120),
                        'inquiryY' => 145 - ($month['inquiries'] / $chartMax * 120),
                    ];
                });
            @endphp
            <div class="chart-wrap">
                <svg class="trend-chart" viewBox="0 0 600 180" role="img" aria-label="{{ __('admin.monthly_trend') }}">
                    <line x1="20" y1="25" x2="580" y2="25" class="chart-gridline" />
                    <line x1="20" y1="85" x2="580" y2="85" class="chart-gridline" />
                    <line x1="20" y1="145" x2="580" y2="145" class="chart-gridline" />
                    <polyline class="chart-line chart-line-submissions" points="{{ $chartPoints->map(fn ($point) => $point['x'].','.$point['submissionY'])->implode(' ') }}" />
                    <polyline class="chart-line chart-line-inquiries" points="{{ $chartPoints->map(fn ($point) => $point['x'].','.$point['inquiryY'])->implode(' ') }}" />
                    @foreach ($chartPoints as $index => $point)
                        <circle class="chart-point chart-point-submissions" cx="{{ $point['x'] }}" cy="{{ $point['submissionY'] }}" r="4"><title>{{ $monthlyTrend[$index]['label'] }}: {{ $monthlyTrend[$index]['submissions'] }} {{ __('admin.clinic_submissions') }}</title></circle>
                        <circle class="chart-point chart-point-inquiries" cx="{{ $point['x'] }}" cy="{{ $point['inquiryY'] }}" r="4"><title>{{ $monthlyTrend[$index]['label'] }}: {{ $monthlyTrend[$index]['inquiries'] }} {{ __('admin.contact_inquiries') }}</title></circle>
                    @endforeach
                </svg>
            </div>
            <div class="chart-labels">
                @foreach ($monthlyTrend as $month)
                    <span>{{ $month['label'] }}</span>
                @endforeach
            </div>
        </article>

        <article class="card analytics-card">
            <h2>{{ __('admin.submission_statuses') }}</h2>
            @foreach ($statusCounts as $status => $count)
                <a class="metric-row status-metric-link" href="{{ route('admin.clinic-submissions.status-list', $status) }}">
                    <span class="status-pill status-{{ $status }}">{{ __('admin.status_'.$status) }}</span>
                    <strong>{{ number_format($count) }}</strong>
                </a>
                <div class="metric-track"><span class="metric-fill status-fill-{{ $status }}" style="width: {{ $clinicSubmissionCount ? ($count / $clinicSubmissionCount * 100) : 0 }}%"></span></div>
            @endforeach
        </article>

        <article class="card analytics-card">
            <h2>{{ __('admin.lead_conversion') }}</h2>
            <div class="conversion-metric">
                <strong>{{ number_format($acceptedRate, 1) }}%</strong>
                <span>{{ __('admin.accepted_submissions') }}</span>
            </div>
            <div class="conversion-metric">
                <strong>{{ number_format($meetingRate, 1) }}%</strong>
                <span>{{ __('admin.meeting_submissions') }}</span>
            </div>
            <p class="analytics-note">{{ __('admin.conversion_note') }}</p>
        </article>

        <article class="card analytics-card">
            <h2>{{ __('admin.top_countries') }}</h2>
            @forelse ($topCountries as $country)
                @php $countryShare = $clinicSubmissionCount ? $country->aggregate / $clinicSubmissionCount * 100 : 0; @endphp
                <div class="country-row">
                    <div><span>{{ $country->country }}</span><strong>{{ number_format((int) $country->aggregate) }}</strong></div>
                    <div class="metric-track"><span class="metric-fill" style="width: {{ $countryShare }}%"></span></div>
                </div>
            @empty
                <p class="analytics-note">{{ __('admin.no_analytics_data') }}</p>
            @endforelse
        </article>
    </section>
@endsection
