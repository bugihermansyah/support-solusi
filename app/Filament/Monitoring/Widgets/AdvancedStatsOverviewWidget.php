<?php

namespace App\Filament\Monitoring\Widgets;

use Carbon\Carbon;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use Filament\Facades\Filament;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 0;
    
    protected int | string | array $columnSpan = '4';

    protected function getStats(): array
    {
        $today = Carbon::now()->toDateString();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

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

        $notif = DB::table('reportings')
            ->join('outstandings', 'outstandings.id', '=', 'reportings.outstanding_id')
            ->where('reporter', 'client')
            ->whereMonth('reportings.date_visit', $currentMonth)
            ->whereYear('reportings.date_visit', $currentYear)
            ->whereNull('send_mail_at')
            ->count();

        return [
            Stat::make('Open Outstandings', $openOutstandings)->icon('heroicon-o-fire')
                ->progress(100)
                ->progressBarColor('danger')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('The outstandings in all times')
                ->descriptionIcon('heroicon-o-information-circle', 'before')
                ->descriptionColor('success')
                ->iconColor('danger'),
            Stat::make('Daily Progress', $totalCount)->icon('heroicon-o-calendar-days')
                ->progress(round($percentage, 2))
                ->progressBarColor('success')
                ->iconPosition('start')
                ->description("The progress in today")
                ->descriptionIcon('heroicon-o-information-circle', 'before')
                ->descriptionColor('success')
                ->iconColor('warning'),
            Stat::make('Implementations', $implementationLocations)->icon('heroicon-o-home')
                ->progress(100)
                ->progressBarColor('success')
                ->iconPosition('start')
                ->description('Location in implementation status')
                ->descriptionIcon('heroicon-o-information-circle', 'before')
                ->descriptionColor('success')
                ->iconColor('warning'),
            Stat::make('Notifications', $notif)->icon('heroicon-o-envelope')
                ->progress(100)
                ->progressBarColor('success')
                ->iconPosition('start')
                ->description("Outstanding notif to client this month")
                ->descriptionIcon('heroicon-o-information-circle', 'before')
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
        return 4;
    }
}
