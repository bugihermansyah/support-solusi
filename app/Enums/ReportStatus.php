<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
// use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ReportStatus: string implements HasColor, HasLabel
{
    case Pending = '0';
    case Finish = '1';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Finish => 'Finish',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Pending => 'danger',
            self::Finish => 'success',
        };
    }

    // public function getIcon(): ?string
    // {
    //     return match ($this) {
    //         self::New => 'heroicon-m-sparkles',
    //         self::Processing => 'heroicon-m-arrow-path',
    //         self::Shipped => 'heroicon-m-truck',
    //         self::Delivered => 'heroicon-m-check-badge',
    //         self::Cancelled => 'heroicon-m-x-circle',
    //     };
    // }
}
