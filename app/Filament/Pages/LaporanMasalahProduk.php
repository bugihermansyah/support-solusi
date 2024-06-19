<?php

// App\Filament\Pages\LaporanMasalahProduk.php

namespace App\Filament\Pages;

use App\Models\Product;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LaporanMasalahProduk extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected $model = \App\Models\Product::class;

    public function getTableRecordKey($record): string
    {
        return (string) $record->getKeyName();
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.laporan-masalah-produk';

    protected static ?string $navigationGroup = 'Reports';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->select('products.name')
                    ->selectRaw('COUNT(outstandings.id) as outstanding_count')
                    ->join('outstandings', 'products.id', '=', 'outstandings.product_id')
                    ->groupBy('products.name')
                    ->orderBy('sort', 'asc')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Produk')
                    ->searchable(),
                TextColumn::make('outstanding_count')
                    ->label('Qty'),
            ])
            ->defaultSort('sort', 'desc')
            ->filters([
                SelectFilter::make('month')
                    ->label('Month')
                    ->options([
                        '01' => 'January',
                        '02' => 'February',
                        '03' => 'March',
                        '04' => 'April',
                        '05' => 'May',
                        '06' => 'June',
                        '07' => 'July',
                        '08' => 'August',
                        '09' => 'September',
                        '10' => 'October',
                        '11' => 'November',
                        '12' => 'December',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereMonth('outstandings.date_in', $data['value']);
                        }
                    }),
                SelectFilter::make('year')
                    ->label('Year')
                    ->options(function () {
                        $years = range(Carbon::now()->year, Carbon::now()->subYears(10)->year);
                        return array_combine($years, $years);
                    })
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereYear('outstandings.date_in', $data['value']);
                        }
                    }),
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }
    // public function table(Table $table): Table
    // {
    //     return $table
    //         ->query(function () {
    //             $year = 2024;  // Tahun yang digunakan dalam query

    //             return DB::table('products as pr')
    //                 ->leftJoin('problems as p', 'pr.id', '=', 'p.product_id')
    //                 ->select(
    //                     'pr.id as product_id',
    //                     'pr.name as product_name',
    //                     DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 1 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_jan"),
    //                     DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 2 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_feb"),
    //                     DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 3 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_mar"),
    //                     DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 4 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_apr"),
    //                     DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 5 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_may"),
    //                     DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 6 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_jun"),
    //                     DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 7 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_jul"),
    //                     DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 8 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_aug"),
    //                     DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 9 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_sep"),
    //                     DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 10 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_oct"),
    //                     DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 11 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_nov"),
    //                     DB::raw("COUNT(CASE WHEN MONTH(p.date_in) = 12 AND YEAR(p.date_in) = $year AND p.reporter = 'client' THEN p.id END) AS total_problems_dec")
    //                 )
    //                 ->groupBy('pr.id', 'pr.name');
    //         })
    //         ->columns([
    //             TextColumn::make('product_id')->label('Product ID'),
    //             TextColumn::make('product_name')->label('Product Name'),
    //             TextColumn::make('total_problems_jan')->label('Jan'),
    //             TextColumn::make('total_problems_feb')->label('Feb'),
    //             TextColumn::make('total_problems_mar')->label('Mar'),
    //             TextColumn::make('total_problems_apr')->label('Apr'),
    //             TextColumn::make('total_problems_may')->label('May'),
    //             TextColumn::make('total_problems_jun')->label('Jun'),
    //             TextColumn::make('total_problems_jul')->label('Jul'),
    //             TextColumn::make('total_problems_aug')->label('Aug'),
    //             TextColumn::make('total_problems_sep')->label('Sep'),
    //             TextColumn::make('total_problems_oct')->label('Oct'),
    //             TextColumn::make('total_problems_nov')->label('Nov'),
    //             TextColumn::make('total_problems_dec')->label('Dec'),
    //         ]);
    // }
}
