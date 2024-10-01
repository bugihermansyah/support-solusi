<?php

namespace App\Filament\Monitoring\Widgets;

use Carbon\Carbon;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use Filament\Facades\Filament;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::now()->toDateString();

        $openOutstandings = DB::table('outstandings')
            ->where('status', 0)
            ->count();

        $implementationLocations = DB::table('locations')
            ->where('status', 'imple')
            ->count();

        $dailyTarget = DB::table('reportings')
            ->where('date_visit', $today);

        // Get the total count of reportings for today
        $totalCount = $dailyTarget->count();

        // Get the count of reportings with a non-null status
        $dailyProgress = $dailyTarget->whereNotNull('status')->count();

        // Calculate the percentage only if totalCount is greater than 0
        if ($totalCount > 0) {
            $percentage = ($dailyProgress / $totalCount) * 100;
        } else {
            $percentage = 0; // Or set it to another default value as needed
        }

        return [
            Stat::make('Open Outstandings', $openOutstandings)->icon('heroicon-o-fire')
                ->progress(100)
                ->progressBarColor('danger')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('The users in this period')
                ->descriptionIcon('heroicon-o-information-circle', 'before')
                ->descriptionColor('success')
                ->iconColor('danger'),
            Stat::make('Daily Progress', $totalCount)->icon('heroicon-o-calendar-days')
                ->progress(round($percentage, 2))
                ->progressBarColor('success')
                ->iconPosition('start')
                ->description("The comments in this period")
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('success')
                ->iconColor('danger'),
            Stat::make('Implementations', $implementationLocations)->icon('heroicon-o-home')
                ->progress(100)
                ->progressBarColor('success')
                ->iconPosition('start')
                ->description('Location in implementation status')
                ->descriptionIcon('heroicon-o-paper-airplane', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Daily Progress', $totalCount)->icon('heroicon-o-calendar-days')
                ->progress(round($percentage, 2))
                ->progressBarColor('success')
                ->iconPosition('start')
                ->description("The comments in this period")
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('success')
                ->iconColor('danger'),
            Stat::make('Daily Progress', $totalCount)->icon('heroicon-o-calendar-days')
                ->progress(round($percentage, 2))
                ->progressBarColor('success')
                ->iconPosition('start')
                ->description("The comments in this period")
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('success')
                ->iconColor('danger'),
        ];
    }

    public static function canView(): bool
    {
        // Tampilkan hanya di panel 'monitoring'
        return Filament::getCurrentPanel() === Filament::getPanel('monitoring');
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
