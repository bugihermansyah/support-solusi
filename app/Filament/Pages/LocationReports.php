<?php

namespace App\Filament\Pages;

use App\Models\Reporting;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Tables\Grouping\Group;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;

class LocationReports extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $model = Reporting::class;

    protected static ?string $slug = 'reporting/monthly/location-report';

    protected static ?string $navigationLabel = 'Laporan Lokasi';

    protected static ?string $title = 'Laporan Lokasi';

    protected ?string $heading = 'Laporan Lokasi';

    public function getTableRecordKey($record): string
    {
        return (string) $record->getKeyName();
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.location-reports';

    protected static ?string $navigationGroup = 'Reports';

    public static function table(Table $table): Table
    {
        // $currentMonth = Carbon::now()->format('m');
        // $currentYear = Carbon::now()->format('Y');

        return $table
            ->query(Reporting::query())
            ->groups([
                Group::make('outstanding.id')
                    ->label('Outstanding')
                    ->getTitleFromRecordUsing(fn (Reporting $record): string => ucfirst($record->outstanding->location->name). ' | Produk: ' .($record->outstanding->product->name). ' | Outstanding: ' .($record->outstanding->title). ' | Lapor: ' .($record->outstanding->date_in). ' | Pelapor: ' .($record->outstanding->reporter))
                    // ->getTitleFromRecordUsing(fn (Reporting $record): string => $record->outstanding
                    //     ? ucfirst($record->outstanding->title) . ' - ' . $record->outstanding->reporter
                    //     : 'No Title')
                    // ->getDescriptionFromRecordUsing(fn (Reporting $record): string => $record->outstanding->date_in)
                    ->collapsible(),
            ])
            ->defaultGroup('outstanding.id')
            ->columns([
                TextColumn::make('date_visit')
                    ->date(),
                TextColumn::make('user.firstname')
                    ->label('Support'),
                TextColumn::make('work')
                    ->label('Tipe Aksi'),
                TextColumn::make('action')
                    ->label('Aksi')
                    ->html(),
                TextColumn::make('solution')
                    ->label('Solusi')
                    ->html()
                    ->wrap(),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                // SelectFilter::make('outstanding_month')
                //     ->label('Lapor bulan')
                //     ->options([
                //         '01' => 'Januari',
                //         '02' => 'Februari',
                //         '03' => 'Maret',
                //         '04' => 'April',
                //         '05' => 'Mei',
                //         '06' => 'Juni',
                //         '07' => 'Juli',
                //         '08' => 'Agustus',
                //         '09' => 'September',
                //         '10' => 'Oktober',
                //         '11' => 'November',
                //         '12' => 'Desember',
                //     ])
                //     ->query(function (Builder $query, array $data) {
                //         return $query->whereHas('outstanding', function (Builder $query) use ($data) {
                //             $query->whereMonth('date_in', $data['value']);
                //         });
                //     })
                //     ->default($currentMonth)
                //     ->query(function (Builder $query, array $data) {
                //         return $query->whereHas('outstading', function (Builder $query) use ($data) {
                //             $query->whereMonth('date_in', $data['value']);
                //         });
                //     }),

                // SelectFilter::make('problem_year')
                //     ->label('Lapor tahun')
                //     ->options(function () {
                //         $years = range(Carbon::now()->year, 2022);
                //         return array_combine($years, $years);
                //     })
                //     ->query(function (Builder $query, array $data) {
                //         return $query->whereHas('problem', function (Builder $query) use ($data) {
                //             $query->whereYear('date_in', $data['value']);
                //         });
                //     }),
                //     // ->default($currentYear)
                //     // ->query(function (Builder $query, array $data) {
                //     //     return $query->whereHas('problem', function (Builder $query) use ($data) {
                //     //         $query->whereYear('date_in', $data['value']);
                //     //     });
                //     // }),

                // // SelectFilter::make('location_id')
                // //     ->label('Nama Lokasi')
                // //     ->searchable()
                // //     ->options(Location::all()->pluck('name', 'id')->toArray())
                // //     ->query(function (Builder $query, array $data) {
                // //         return $query->where('name', $data['value']);
                // //     }),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
