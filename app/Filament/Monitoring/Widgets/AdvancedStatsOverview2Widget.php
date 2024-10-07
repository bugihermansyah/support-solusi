<?php

namespace App\Filament\Monitoring\Widgets;

use Carbon\Carbon;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use Filament\Facades\Filament;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AdvancedStatsOverview2Widget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = '1';

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $allLocations = DB::table('locations')
            ->where('type_contract', 'sewa')
            ->whereNot('status', 'dismantle')
            ->count();

        return [
            Stat::make('All Locations', $allLocations)->icon('heroicon-o-map-pin')
                ->progress(100)
                ->progressBarColor('success')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Rental loations in all teams')
                ->descriptionIcon('heroicon-o-information-circle', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
        ];
    }

    public static function canView(): bool
    {
        // Tampilkan hanya di panel 'monitoring'
        return Filament::getCurrentPanel() === Filament::getPanel('monitoring');
    }

    protected function getColumns(): int
    {
        return 1;
    }
}
