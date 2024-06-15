<?php

// App\Filament\Pages\LaporanMasalahProduk.php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class LaporanMasalahProduk extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $year = 2024;  // Tahun yang digunakan dalam query

                return DB::table('products as pr')
                    ->leftJoin('problems as p', 'pr.id', '=', 'p.product_id')
                    ->select(
                        'pr.id as product_id',
                        'pr.name as product_name',
                        DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 1 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_jan"),
                        DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 2 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_feb"),
                        DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 3 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_mar"),
                        DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 4 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_apr"),
                        DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 5 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_may"),
                        DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 6 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_jun"),
                        DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 7 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_jul"),
                        DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 8 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_aug"),
                        DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 9 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_sep"),
                        DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 10 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_oct"),
                        DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 11 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_nov"),
                        DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 12 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_dec")
                    )
                    ->groupBy('pr.id', 'pr.name');
            })
            ->columns([
                TextColumn::make('product_id')->label('Product ID'),
                TextColumn::make('product_name')->label('Product Name'),
                TextColumn::make('total_problems_jan')->label('Jan'),
                TextColumn::make('total_problems_feb')->label('Feb'),
                TextColumn::make('total_problems_mar')->label('Mar'),
                TextColumn::make('total_problems_apr')->label('Apr'),
                TextColumn::make('total_problems_may')->label('May'),
                TextColumn::make('total_problems_jun')->label('Jun'),
                TextColumn::make('total_problems_jul')->label('Jul'),
                TextColumn::make('total_problems_aug')->label('Aug'),
                TextColumn::make('total_problems_sep')->label('Sep'),
                TextColumn::make('total_problems_oct')->label('Oct'),
                TextColumn::make('total_problems_nov')->label('Nov'),
                TextColumn::make('total_problems_dec')->label('Dec'),
            ]);
    }
}
