<?php

namespace App\Filament\Widgets;

use App\Models\Reporting;
use App\Models\Outstanding;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\Auth;

class ReportingsChart extends ChartWidget
{
    protected static ?string $heading = 'Outstanding';

    protected static ?int $sort = 1;

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Mingguan',
            'month' => 'Bulanan',
            'year' => 'Tahunan',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter ?? 'week';
        $user = Auth::user();
        $team = $user->team;
        $locationIds = $team->locations->pluck('id');

        switch ($filter) {
            case 'week':
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
                $interval = 'perDay';
                break;
            case 'month':
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
                $interval = 'perDay';
                break;
            case 'year':
            default:
                $start = now()->startOfYear();
                $end = now()->endOfYear();
                $interval = 'perMonth';
                break;
        }

            $reportingData = Trend::query(
                Reporting::whereHas('outstanding', function ($query) use ($locationIds) {
                    $query->whereIn('location_id', $locationIds);
                })
            )
            ->between(start: $start, end: $end)
            ->$interval()
            ->count();

        $outstandingData = Trend::query(Outstanding::whereIn('location_id', $locationIds))
            ->between(start: $start, end: $end)
            ->$interval()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Reporting',
                    'data' => $reportingData->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                    'borderColor' => 'rgb(235, 52, 52)',
                    'tension' => 0.1,
                    'fill' => false,
                ],
                [
                    'label' => 'Outstanding',
                    'data' => $outstandingData->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                    'borderColor' => 'rgb(56, 191, 52)',
                    'tension' => 0.1,
                    'fill' => false,
                ],
            ],
            'labels' => $reportingData->map(fn (TrendValue $value) => $value->date)->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
