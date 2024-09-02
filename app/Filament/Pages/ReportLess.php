<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Widgets\ReportLessChart;
use Filament\Pages\Page;

class ReportLess extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.report-less';

    protected static ?string $title = 'Summary Report';

    protected ?string $heading = '';

    protected static ?string $slug = 'reporting/monthly/report-less';

    protected static ?string $navigationGroup = 'Reports';

    protected function getHeaderWidgets(): array
    {
        return [
            ReportLessChart::class
        ];
    }
}
