<?php

namespace App\Filament\Pages;

use App\Models\Outstanding;
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

class AdminSLAFinishGroup extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $slug = 'reporting/monthly/admin/sla-finish-group';

    protected static ?string $navigationLabel = 'SLA Finish Group';

    protected static ?string $title = 'Admin SLA Finish Group';

    protected ?string $heading = 'SLA Finish Group';

    public function getTableRecordKey($record): string
    {
        return (string) $record->getKeyName();
    }

    protected static string $view = 'filament.pages.sla-finish';

    protected static ?string $navigationGroup = 'Reports';

    protected static function getCurrentFilters()
    {
        return array_filter(request()->query('filter', []), function ($value) {
            return $value !== null && $value !== '';
        });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Outstanding::query()
                    ->selectRaw("
                        MONTH(outstandings.date_visit) as month,

                        SUM(CASE
                            WHEN outstandings.date_finish IS NOT NULL
                            AND DATEDIFF(outstandings.date_finish, outstandings.date_visit) BETWEEN 0 AND 3
                            THEN 1 ELSE 0 END) as sla1_count,

                        SUM(CASE
                            WHEN outstandings.date_finish IS NOT NULL
                            AND DATEDIFF(outstandings.date_finish, outstandings.date_visit) BETWEEN 4 AND 7
                            THEN 1 ELSE 0 END) as sla2_count,

                        SUM(CASE
                            WHEN outstandings.date_finish IS NOT NULL
                            AND DATEDIFF(outstandings.date_finish, outstandings.date_visit) > 7
                            THEN 1 ELSE 0 END) as sla3_count,

                        SUM(CASE
                            WHEN outstandings.date_finish IS NULL
                            THEN 1 ELSE 0 END) as not_finish_count,

                        SUM(CASE
                            WHEN outstandings.date_finish IS NOT NULL
                            AND outstandings.status = 1
                            THEN 1 ELSE 0 END) as temporary_count
                    ")
                    ->join('products', 'products.id', '=', 'outstandings.product_id')
                    ->join('locations', 'locations.id', '=', 'outstandings.location_id')
                    ->whereNotNull('outstandings.date_visit')
                    // ->where('locations.team_id', $userTeam)

                    // ->whereIn('products.group', ['cass', 'manless', 'other'])

                    ->groupByRaw('MONTH(outstandings.date_visit)')
                    ->orderByRaw('MONTH(outstandings.date_visit)')
            )
            ->columns([
                Tables\Columns\TextColumn::make('month')
        ->label('Bulan')
        ->formatStateUsing(fn ($state) =>
            Carbon::create()->month($state)->translatedFormat('F')
        ),
                Tables\Columns\TextColumn::make('sla1_count')
                    ->label('SLA 1 (0 - 3)')
                    ->summarize(Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('sla2_count')
                    ->label('SLA 2 (4 - 7)')
                    ->summarize(Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('sla3_count')
                    ->label('SLA 3 (> 7)')
                    ->openUrlInNewTab()
                    ->summarize(Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('not_finish_count')
                    ->label('Not Finish')
                    ->color('danger')
                    ->summarize(Sum::make()->label('Total')),

                Tables\Columns\TextColumn::make('temporary_count')
                    ->label('Temporary')
                    ->color('warning')
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
                        if (!empty($data['value'])) {
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
                        if (!empty($data['value'])) {
                            $query->whereYear('outstandings.date_in', $data['value']);
                        }
                    }),
                SelectFilter::make('group')
                ->label('Product Group')
                ->options([
                    'cass' => 'Cass',
                    'manless' => 'Manless',
                    'other' => 'Other',
                ])
                ->default(null) // atau 'cass' kalau mau default
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['value'])) {
                        $query->where('products.group', $data['value']);
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
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
