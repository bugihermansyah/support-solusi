<?php

namespace App\Filament\Pages;

use App\Models\OutstandingUnit;
use App\Models\Team;
use App\Models\Unit;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Resources\Components\Tab;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LaporanKerusakanUnit extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $slug = 'reporting/monthly/kerusakan-unit';

    protected static ?string $navigationLabel = 'Kerusakan Unit';

    // protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.laporan-kerusakan-unit';

    protected static ?string $navigationGroup = 'Reports';

    public function getTableRecordKey($record): string
    {
        return (string) $record->getKeyName();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OutstandingUnit::query()
                    ->select('units.name')
                    ->selectRaw('SUM(outstanding_units.qty) AS unit_sums')
                    ->selectRaw('SUM(CASE WHEN outstandings.is_type_problem = 1 THEN outstanding_units.qty ELSE 0 END) AS hw')
                    ->selectRaw('SUM(CASE WHEN outstandings.is_type_problem = 2 THEN outstanding_units.qty ELSE 0 END) AS sw')
                    ->selectRaw('SUM(CASE WHEN outstandings.is_type_problem = 3 THEN outstanding_units.qty ELSE 0 END) AS hwnon')
                    ->selectRaw('SUM(CASE WHEN outstandings.is_type_problem = 4 THEN outstanding_units.qty ELSE 0 END) AS sipil')
                    ->join('units', 'units.id', '=', 'outstanding_units.unit_id')
                    ->join('outstandings', 'outstandings.id', '=', 'outstanding_units.outstanding_id')
                    ->leftjoin('locations', 'locations.id', '=', 'outstandings.location_id')
                    ->groupBy('units.name')
                    // ->orderBy('units.sort', 'asc')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Unit')
                    ->sortable(),
                TextColumn::make('unit_sums')
                    ->label('All')
                    ->summarize(Sum::make()->label('Total'))
                    ->sortable(),
                TextColumn::make('hw')
                    ->label('H/W')
                    ->summarize(Sum::make()->label('Total'))
                    ->sortable(),
                TextColumn::make('sw')
                    ->label('S/W')
                    ->summarize(Sum::make()->label('Total'))
                    ->sortable(),
                TextColumn::make('hwnon')
                    ->label('H/W-Non')
                    ->summarize(Sum::make()->label('Total'))
                    ->sortable(),
                TextColumn::make('sipil')
                    ->label('Sipil')
                    ->summarize(Sum::make()->label('Total'))
                    ->sortable(),
            ])
            ->defaultSort('unit_sums', 'desc')
            ->filters([
                SelectFilter::make('month')
                    ->label('Bulan')
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
                        if (!empty($data['value'])) {
                            $query->whereMonth('outstandings.date_in', $data['value']);
                        }
                    }),
                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(function () {
                        $years = range(Carbon::now()->year, 2021);
                        return array_combine($years, $years);
                    })
                    ->default(Carbon::now()->year)
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereYear('outstandings.date_in', $data['value']);
                        }
                    }),
                SelectFilter::make('team')
                    ->label('Tim')
                    ->options(Team::all()->pluck('name', 'id'))
                    ->default(Auth::user()->team_id)
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
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
            // , layout: FiltersLayout::AboveContent)
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }
}
