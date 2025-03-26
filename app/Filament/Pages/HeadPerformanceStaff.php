<?php

namespace App\Filament\Pages;

use App\Models\Reporting;
use App\Models\Team;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class HeadPerformanceStaff extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $slug = 'reporting/monthly/head/performance-staff';

    protected static ?string $navigationLabel = 'Performance Staff';

    protected static ?string $title = 'Head Performance Staff';

    public function getTableRecordKey($record): string
    {
        return (string) $record->getKeyName();
    }

    protected static string $view = 'filament.pages.performance-staff';

    protected static ?string $navigationGroup = 'Reports';

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $userTeam = $user ? $user->getTeamId() : null ;

        return $table
            ->query(
                    Reporting::query()
                    ->join('reporting_users', 'reportings.id', '=', 'reporting_users.reporting_id')
                    ->join('users', 'reporting_users.user_id', '=', 'users.id')
                    ->join('outstandings', 'reportings.outstanding_id', '=', 'outstandings.id')
                    ->leftJoin('locations', function ($join) {
                        $join->on('outstandings.location_id', '=', 'locations.id')
                            ->on('locations.user_id', '=', 'users.id'); // Hanya lokasi milik support
                    })
                    ->where('locations.team_id', $userTeam)
                    ->selectRaw("
                        users.firstname AS support,
                        COUNT(DISTINCT locations.id) AS lokasi, -- Hitung hanya lokasi milik support
                        SUM(CASE WHEN reportings.work = 'visit' THEN 1 ELSE 0 END) AS visit, -- Tidak bergantung lokasi
                        SUM(CASE WHEN reportings.work = 'remote' THEN 1 ELSE 0 END) AS remote, -- Tidak bergantung lokasi
                        COUNT(DISTINCT CASE WHEN locations.id IS NOT NULL THEN outstandings.id END) AS laporan_masalah -- Hanya hitung masalah dari lokasi support
                    ")
                    ->groupBy('users.id', 'users.firstname')
                    ->orderByDesc('laporan_masalah')
            )
            ->columns([
                TextColumn::make('support')->label('Support')->sortable(),
                TextColumn::make('lokasi')->label('Lokasi')->sortable(),
                TextColumn::make('laporan_masalah')->label('O. Lokasi')->sortable(),
                TextColumn::make('visit')->label('Visit')->sortable(),
                TextColumn::make('remote')->label('Remote')->sortable(),
            ])
            // ->defaultSort('sort', 'asc')
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
                // SelectFilter::make('pelapor')
                //     ->label('Pelapor')
                //     ->options([
                //         'client' => 'Client',
                //         'preventif' => 'Preventif',
                //         'support' => 'Support'
                //     ])
                //     ->query(function (Builder $query, array $data) {
                //         if (!empty($data['value'])) {
                //             $query->where('outstandings.reporter', $data['value']);
                //         }
                //     }),
                // TernaryFilter::make('lpm')
                //     ->label('Laporan /1 masuk'),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
