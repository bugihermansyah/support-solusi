<?php

namespace App\Filament\Monitoring\Widgets;

use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;

class DailyChartWidget extends AdvancedChartWidget
{

    protected static ?string $heading = null;
    protected static string $color = 'info';
    protected static ?string $icon = 'heroicon-o-chart-bar';
    protected static ?string $iconColor = 'info';
    protected static ?string $iconBackgroundColor = 'info';
    protected static ?string $label = 'This monthly daily supports';

    protected static ?string $badge = 'new';
    protected static ?string $badgeColor = 'success';
    // protected static ?string $badgeIcon = 'heroicon-o-check-circle';
    protected static ?string $badgeIconPosition = 'after';
    protected static ?string $badgeSize = 'xs';

    public function getHeading(): string | Htmlable | null
    {
        // Get the current month and year
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Count the reportings for the current month
        $monthlyReportCount = DB::table('reportings')
            ->whereMonth('date_visit', $currentMonth)
            ->whereYear('date_visit', $currentYear)
            ->count();

        // Set the heading as a string
        return (string) $monthlyReportCount; // Return as string
    }

    protected function getData(): array
    {
        // Get the current month and year
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Get the number of days in the current month
        $daysInMonth = Carbon::now()->daysInMonth;

        // Prepare labels and data arrays
        $labels = [];
        $data = [];

        // Loop through each day of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            // Format the date for the current month
            $date = Carbon::createFromDate($currentYear, $currentMonth, $day);

            // Add the date to labels
            $labels[] = $date->format('d'); // Display day number

            // Count the reportings for that specific day
            $dailyCount = DB::table('reportings')
                ->whereDate('date_visit', $date)
                ->count();

            // Add the count to data
            $data[] = $dailyCount;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Daily Reports',
                    'data' => $data,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)', // Example background color
                    'borderColor' => 'rgba(75, 192, 192, 1)', // Example border color
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }


    protected function getType(): string
    {
        return 'line';
    }
}
