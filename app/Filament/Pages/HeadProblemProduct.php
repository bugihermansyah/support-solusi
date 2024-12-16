<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Team;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class HeadProblemProduct extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $slug = 'reporting/monthly/head/problem-product';

    protected static ?string $navigationLabel = 'Problem Product';

    public function getTableRecordKey($record): string
    {
        return (string) $record->getKeyName();
    }

    protected static string $view = 'filament.pages.laporan-masalah-produk';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $title = 'Head Problem Product';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $userTeam = $user ? $user->getTeamId() : null ;

        return $table
            ->query(
                Product::query()
                    ->select('products.name')
                    ->selectRaw('COUNT(outstandings.id) as outstanding_count')
                    ->join('outstandings', 'products.id', '=', 'outstandings.product_id')
                    ->leftjoin('locations', 'locations.id', '=', 'outstandings.location_id')
                    ->where('locations.team_id', $userTeam)
                    ->groupBy('products.name')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('outstanding_count')
                    ->label('Jumlah')
                    ->summarize(Sum::make()->label('Total'))
                    ->sortable(),
            ])
            ->defaultSort('sort', 'asc')
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
                SelectFilter::make('pelapor')
                    ->label('Pelapor')
                    ->options([
                        'client' => 'Client',
                        'preventif' => 'Preventif',
                        'support' => 'Support'
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where('outstandings.reporter', $data['value']);
                        }
                    }),
                TernaryFilter::make('lpm')
                    ->label('Laporan /1 masuk'),
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }
}
