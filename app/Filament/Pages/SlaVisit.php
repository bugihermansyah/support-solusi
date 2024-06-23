<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Team;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SlaVisit extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $slug = 'reporting/monthly/sla-visit';

    protected static ?string $navigationLabel = 'SLA Visit/Remote';

    protected static ?string $title = 'Laporan SLA Visit/Remote';

    public function getTableRecordKey($record): string
    {
        return (string) $record->getKeyName();
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.sla-visit';

    protected static ?string $navigationGroup = 'Reports';

    public static function table(Table $table): Table
    {
        return $table
            ->query(
            Product::query()
                    ->select('products.name')
                    ->selectRaw("
                        SUM(CASE
                            WHEN DATEDIFF(outstandings.date_visit, outstandings.date_in) BETWEEN 0 AND 1 THEN 1
                            ELSE 0
                        END) as sla1_count,
                        SUM(CASE
                            WHEN DATEDIFF(outstandings.date_visit, outstandings.date_in) BETWEEN 2 AND 3 THEN 1
                            ELSE 0
                        END) as sla2_count,
                        SUM(CASE
                            WHEN DATEDIFF(outstandings.date_visit, outstandings.date_in) > 3 THEN 1
                            ELSE 0
                        END) as sla3_count
                    ")
                    ->join('outstandings', 'products.id', '=', 'outstandings.product_id')
                    ->leftjoin('locations', 'locations.id', '=', 'outstandings.location_id')
                    ->whereNotNull('outstandings.date_visit')
                    ->groupBy('products.name')
                    // ->orderBy('products.name')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sla1_count')
                    ->label('SLA 1 (0 - 1)')
                    ->summarize(Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('sla2_count')
                    ->label('SLA 2 (2 - 3)')
                    ->summarize(Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('sla3_count')
                    ->label('SLA 3 (> 3)')
                    ->summarize(Sum::make()->label('Total')),
            ])
            ->defaultSort('sort', 'asc')
            ->filters([
                SelectFilter::make('month')
                    ->label('Month')
                    ->options([
                        '01' => 'Januari',
                        '02' => 'Februari',
                        '03' => 'Maret',
                        '04' => 'April',
                        '05' => 'Mei',
                        '06' => 'Juni',
                        '07' => 'Juli',
                        '08' => 'Agustus',
                        '09' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ])
                    ->default(Carbon::now()->format('m'))
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereMonth('outstandings.date_in', $data['value']);
                        }
                    }),
                SelectFilter::make('year')
                    ->label('Year')
                    ->options(function () {
                        $years = range(Carbon::now()->year, 2021);
                        return array_combine($years, $years);
                    })
                    ->default(Carbon::now()->year)
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereYear('outstandings.date_in', $data['value']);
                        }
                    }),
                SelectFilter::make('team')
                    ->label('Tim')
                    ->options(Team::all()->pluck('name', 'id'))
                    ->default(Auth::user()->team_id)
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->where('locations.team_id', $data['value']);
                        }
                    }),
                SelectFilter::make('pelapor')
                    ->label('Pelapor')
                    ->options([
                        'client' => 'Client',
                        'preventif' => 'Preventif',
                        'support' => 'Support'
                    ])
                    ->default('client')
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where('outstandings.reporter', $data['value']);
                        }
                    }),
                TernaryFilter::make('lpm')
                    ->label('Laporan /1 masuk'),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
