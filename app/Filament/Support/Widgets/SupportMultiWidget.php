<?php

namespace App\Filament\Support\Widgets;

use App\Filament\Support\Pages\Widgets\OpenOutstandings;
use App\Filament\Support\Pages\Widgets\ScheduleOutstandings;
use Kenepa\MultiWidget\MultiWidget;

class SupportMultiWidget extends MultiWidget
{
    public array $widgets = [
        ScheduleOutstandings::class,
        OpenOutstandings::class,
    ];
}
