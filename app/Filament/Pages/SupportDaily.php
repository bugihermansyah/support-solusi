<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Widgets\SupportDailyWidget as WidgetsSupportDaily;
use App\Models\Reporting;
use Filament\Pages\Page;

class SupportDaily extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static string $view = 'filament.pages.support-daily';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Daily';

    public function mount()
    {
        $this->form->fill([
            'widget' => WidgetsSupportDaily::class,
        ]);
    }
}
