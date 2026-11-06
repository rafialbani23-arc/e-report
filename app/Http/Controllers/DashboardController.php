<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardPeriodRequest;
use App\Models\Laporan;
use App\Models\LaporanStatusHistory;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const PERIODS = [
        '7d' => '7 hari terakhir',
        '30d' => '30 hari terakhir',
        '90d' => '90 hari terakhir',
        '12m' => '12 bulan terakhir',
        'all' => 'Semua waktu',
    ];

    public function admin(DashboardPeriodRequest $request): View
    {
        $period = $request->validated()['period'] ?? '30d';
        $periodStart = $this->periodStart($period);
        $periodEnd = CarbonImmutable::now();

        $filteredReports = Laporan::query();
        $this->applyPeriod($filteredReports, $periodStart, $periodEnd);

        $statistics = $this->reportStatistics(clone $filteredReports);
        $monthlyTrend = $this->monthlyTrend($periodStart, $periodEnd);
        $statuses = $this->statusBreakdown($statistics);

        $currentMonthStart = CarbonImmutable::now()->startOfMonth();
        $comparisonDay = min(
            CarbonImmutable::now()->day,
            $currentMonthStart->subMonth()->daysInMonth,
        );
        $previousMonthStart = $currentMonthStart->subMonth();
        $previousMonthEnd = $previousMonthStart
            ->setDay($comparisonDay)
            ->endOfDay();
        $currentMonthReports = Laporan::query()
            ->whereBetween('created_at', [$currentMonthStart, $periodEnd])
            ->count();
        $previousMonthReports = Laporan::query()
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $reports = (clone $filteredReports)
            ->with('pelapor:id,name')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $activities = LaporanStatusHistory::query()
            ->with(['laporan:id,judul,pelapor_id', 'admin:id,name'])
            ->when(
                $periodStart !== null,
                fn (Builder $query) => $query->whereBetween('created_at', [$periodStart, $periodEnd]),
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('dashboard.index', [
            'isAdmin' => true,
            'period' => $period,
            'periods' => self::PERIODS,
            'periodLabel' => self::PERIODS[$period],
            'statistics' => $statistics,
            'residentCount' => User::query()->where('role', 'warga')->count(),
            'monthlyTrend' => $monthlyTrend,
            'trendMaximum' => max(1, max(array_column($monthlyTrend, 'count') ?: [0])),
            'statusBreakdown' => $statuses,
            'comparison' => [
                'current' => $currentMonthReports,
                'previous' => $previousMonthReports,
                'current_label' => 'Bulan ini hingga hari ini',
                'previous_label' => 'Periode setara bulan lalu',
            ],
            'reports' => $reports,
            'activities' => $activities,
        ]);
    }

    public function warga(Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $reportsQuery = $user->laporans()->getQuery();
        $statistics = $this->reportStatistics(clone $reportsQuery);

        $reports = $user->laporans()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $activities = LaporanStatusHistory::query()
            ->whereHas('laporan', fn (Builder $query) => $query->where('pelapor_id', $user->getKey()))
            ->with(['laporan:id,judul,pelapor_id', 'admin:id,name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('dashboard.index', [
            'isAdmin' => false,
            'statistics' => $statistics,
            'reports' => $reports,
            'activities' => $activities,
        ]);
    }

    private function periodStart(string $period): ?CarbonImmutable
    {
        $today = CarbonImmutable::now()->startOfDay();

        return match ($period) {
            '7d' => $today->subDays(6),
            '30d' => $today->subDays(29),
            '90d' => $today->subDays(89),
            '12m' => $today->startOfMonth()->subMonths(11),
            default => null,
        };
    }

    private function applyPeriod(Builder $query, ?CarbonImmutable $start, CarbonImmutable $end): void
    {
        if ($start !== null) {
            $query->whereBetween('created_at', [$start, $end]);
        }
    }

    /**
     * @return array{total: int, pending: int, approved: int, rejected: int}
     */
    private function reportStatistics(Builder|QueryBuilder $query): array
    {
        $statistics = $query
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END), 0) as approved")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END), 0) as rejected")
            ->first();

        return [
            'total' => (int) ($statistics?->total ?? 0),
            'pending' => (int) ($statistics?->pending ?? 0),
            'approved' => (int) ($statistics?->approved ?? 0),
            'rejected' => (int) ($statistics?->rejected ?? 0),
        ];
    }

    /**
     * @param  array{total: int, pending: int, approved: int, rejected: int}  $statistics
     * @return array<int, array{key: string, label: string, count: int}>
     */
    private function statusBreakdown(array $statistics): array
    {
        return collect(['pending', 'approved', 'rejected'])
            ->map(fn (string $status): array => [
                'key' => $status,
                'label' => Laporan::STATUSES[$status],
                'count' => $statistics[$status],
            ])
            ->all();
    }

    /**
     * @return array<int, array{key: string, label: string, count: int}>
     */
    private function monthlyTrend(?CarbonImmutable $start, CarbonImmutable $end): array
    {
        $driver = DB::connection()->getDriverName();
        $yearExpression = match ($driver) {
            'sqlite' => "CAST(strftime('%Y', created_at) AS INTEGER)",
            default => 'YEAR(created_at)',
        };
        $monthExpression = match ($driver) {
            'sqlite' => "CAST(strftime('%m', created_at) AS INTEGER)",
            default => 'MONTH(created_at)',
        };

        $monthlyCounts = DB::table('laporans')
            ->selectRaw("{$yearExpression} as report_year")
            ->selectRaw("{$monthExpression} as report_month")
            ->selectRaw('COUNT(*) as report_count')
            ->whereNotNull('created_at')
            ->when(
                $start !== null,
                fn (QueryBuilder $query) => $query->whereBetween('created_at', [$start, $end]),
            )
            ->groupByRaw($yearExpression.', '.$monthExpression)
            ->orderBy('report_year')
            ->orderBy('report_month')
            ->get()
            ->keyBy(fn (object $row): string => sprintf('%04d-%02d', $row->report_year, $row->report_month));

        if ($monthlyCounts->isEmpty()) {
            return [];
        }

        $firstMonth = $start?->startOfMonth()
            ?? CarbonImmutable::createFromFormat('Y-m', (string) $monthlyCounts->keys()->first())->startOfMonth();
        $lastMonth = $end->startOfMonth();
        $trend = [];

        for ($month = $firstMonth; $month <= $lastMonth; $month = $month->addMonth()) {
            $key = $month->format('Y-m');
            $trend[] = [
                'key' => $key,
                'label' => $month->locale('id')->translatedFormat('M y'),
                'count' => (int) ($monthlyCounts->get($key)?->report_count ?? 0),
            ];
        }

        return $trend;
    }
}
