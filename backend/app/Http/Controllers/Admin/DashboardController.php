<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicSubmission;
use App\Models\ContactInquiry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $monthlyTrend = $this->monthlyTrend();
        $statusCounts = ClinicSubmission::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $statusCounts = collect(['pending', 'accepted', 'refused', 'meeting'])
            ->mapWithKeys(fn (string $status) => [$status => (int) $statusCounts->get($status, 0)]);
        $submissionCount = ClinicSubmission::count();

        return view('admin.dashboard', [
            'clinicSubmissionCount' => $submissionCount,
            'contactInquiryCount' => ContactInquiry::count(),
            'adminAccountCount' => auth()->user()->can('admin-users.manage')
                ? User::whereHas('roles')->count()
                : null,
            'statusCounts' => $statusCounts,
            'acceptedRate' => $submissionCount === 0
                ? 0
                : ($statusCounts->get('accepted') / $submissionCount) * 100,
            'meetingRate' => $submissionCount === 0
                ? 0
                : ($statusCounts->get('meeting') / $submissionCount) * 100,
            'monthlyTrend' => $monthlyTrend,
            'averageSubmissionsPerMonth' => $monthlyTrend->avg('submissions'),
            'averageInquiriesPerMonth' => $monthlyTrend->avg('inquiries'),
            'topCountries' => ClinicSubmission::query()
                ->selectRaw('country, COUNT(*) as aggregate')
                ->groupBy('country')
                ->orderByDesc('aggregate')
                ->limit(5)
                ->get(),
        ]);
    }

    private function monthlyTrend(): Collection
    {
        $firstMonth = CarbonImmutable::now()->startOfMonth()->subMonths(5);

        return collect(range(0, 5))->map(function (int $offset) use ($firstMonth): array {
            $start = $firstMonth->addMonths($offset);
            $end = $start->addMonth();

            return [
                'label' => $start->locale(app()->getLocale())->translatedFormat('M'),
                'submissions' => ClinicSubmission::query()
                    ->where('created_at', '>=', $start)
                    ->where('created_at', '<', $end)
                    ->count(),
                'inquiries' => ContactInquiry::query()
                    ->where('created_at', '>=', $start)
                    ->where('created_at', '<', $end)
                    ->count(),
            ];
        });
    }
}
