<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Widgets\OpenImplementasi;
use App\Filament\Pages\Widgets\OpenOutstandings;
use Kenepa\MultiWidget\MultiWidget;

class MultiTabWidget extends MultiWidget
{
    protected static ?int $sort = 2;

    public array $widgets = [
        OpenOutstandings::class,
        OpenImplementasi::class,
    ];
}
