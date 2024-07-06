<?php

namespace App\Filament\Support\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    // use BaseDashboard\Concerns\HasFiltersForm;
    protected static string $routePath = 'support';

    protected ?string $heading = '';

    // protected function getYearOptions(): array
    // {
    //     $years = range(date('Y'), date('Y') - 5);
    //     return array_combine($years, $years);
    // }

    // public function filtersForm(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Section::make()
    //                 ->schema([
    //                     Select::make('typeContract')
    //                         ->label('Kontrak Lokasi')
    //                         ->options([
    //                             'all' => 'Semua',
    //                             'sewa' => 'Sewa',
    //                             'putus' => 'Putus'
    //                         ])
    //                         ->selectablePlaceholder(false)
    //                         ->default('all'),

    //                     Select::make('month')
    //                         ->label('Bulan')
    //                         ->options([
    //                             '01' => 'Januari',
    //                             '02' => 'Februari',
    //                             '03' => 'Maret',
    //                             '04' => 'April',
    //                             '05' => 'Mei',
    //                             '06' => 'Juni',
    //                             '07' => 'Juli',
    //                             '08' => 'Agustus',
    //                             '09' => 'September',
    //                             '10' => 'Oktober',
    //                             '11' => 'November',
    //                             '12' => 'Desember',
    //                         ]),

    //                     Select::make('year')
    //                         ->label('Tahun')
    //                         ->options($this->getYearOptions()),
    //                 ])
    //                 ->columns(5),
    //         ]);
    // }
}
