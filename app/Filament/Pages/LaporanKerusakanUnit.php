<?php

namespace App\Filament\Pages;

use App\Models\OutstandingUnit;
use App\Models\Unit;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LaporanKerusakanUnit extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

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
                    ->selectRaw('SUM(outstanding_units.qty) as unit_sums')
                    ->join('units', 'units.id', '=', 'outstanding_units.unit_id')
                    ->join('outstandings', 'outstandings.id', '=', 'outstanding_units.outstanding_id')
                    ->groupBy('units.name')
                    // ->orderBy('sort', 'asc')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Produk')
                    ->searchable(),
                TextColumn::make('unit_sums')
                    ->label('Qty'),
            ])
            ->defaultSort('name', 'asc')
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
}
