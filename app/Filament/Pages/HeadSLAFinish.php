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

class HeadSLAFinish extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $slug = 'reporting/monthly/head/sla-finish';

    protected static ?string $navigationLabel = 'SLA Finish';

    protected static ?string $title = 'Head SLA Finish';

    protected ?string $heading = 'SLA Finish';

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
        $user = Auth::user();
        $userTeam = $user ? $user->getTeamId() : null ;

        return $table
            ->query(
            Product::query()
                ->select('products.name')
                    ->selectRaw("
                        SUM(CASE
                            WHEN DATEDIFF(outstandings.date_finish, outstandings.date_visit) BETWEEN 0 AND 3 THEN 1
                            ELSE 0
                        END) as sla1_count,
                        SUM(CASE
                            WHEN DATEDIFF(outstandings.date_finish, outstandings.date_visit) BETWEEN 4 AND 7 THEN 1
                            ELSE 0
                        END) as sla2_count,
                        SUM(CASE
                            WHEN DATEDIFF(outstandings.date_finish, outstandings.date_visit) > 7 THEN 1
                            ELSE 0
                        END) as sla3_count,
                        SUM(CASE
                            WHEN outstandings.date_finish IS NULL THEN 1
                            ELSE 0
                        END) as not_finish_count,
                        SUM(CASE
                            WHEN outstandings.date_finish IS NOT NULL
                            AND outstandings.is_temporary = 1 THEN 1
                            ELSE 0
                        END) as temporary_count
                    ")
                    ->join('outstandings', 'products.id', '=', 'outstandings.product_id')
                    ->leftjoin('locations', 'locations.id', '=', 'outstandings.location_id')
                    // ->whereNotNull('outstandings.date_finish')
                    ->whereNotNull('outstandings.date_visit')
                    ->where('locations.team_id', $userTeam)
                    ->groupBy('products.name')
                    
                )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sla1_count')
                    ->label('SLA 1 (0 - 3)')
                    ->url(fn ($record) => route('filament.admin.resources.outstandings.index', array_merge(request()->query(), [
                        'tableFilters[sla][value]' => 'sla1',
                        'tableFilters[month][value]' => request()->input('filter.month.value'),
                        'tableFilters[year][value]' => request()->input('filter.year.value'),
                        'tableFilters[team][value]' => request()->input('filter.team.value'),
                        'tableFilters[pelapor][value]' => request()->input('filter.pelapor.value'),
                        'tableFilters[lpm][value]' => request()->input('filter.lpm.value'),
                    ])))
                    ->openUrlInNewTab()
                    ->summarize(Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('sla2_count')
                    ->label('SLA 2 (4 - 7)')
                    ->summarize(Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('sla3_count')
                    ->label('SLA 3 (> 7)')
                    ->url(fn ($record) => route('filament.admin.resources.outstandings.index', array_merge(self::getCurrentFilters(), [
                        'filter[sla][value]' => 'sla3',
                    ])))
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
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
