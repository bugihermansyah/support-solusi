<?php

namespace App\Enums;

// use Filament\Support\Contracts\HasColor;
// use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TypeContract: string implements HasLabel
{
    case Sewa = 'sewa';
    case Putus = 'putus';
    case Kredit = 'kredit';

    public function getLabel(): string
    {
        return match ($this) {
            self::Sewa => 'Sewa',
            self::Putus => 'Putus',
            self::Kredit => 'Kredit',
        };
    }

    // public function getColor(): string | array | null
    // {
    //     return match ($this) {
    //         self::Implementasi => 'warning',
    //         self::New => 'info',
    //         self::Cancelled => 'danger',
    //     };
    // }

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
