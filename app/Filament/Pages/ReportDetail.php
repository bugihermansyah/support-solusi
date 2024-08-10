<?php

namespace App\Filament\Pages;

use App\Models\Location;
use App\Models\Product;
use App\Models\Reporting;
use App\Models\Team;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ReportDetail extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $model = Reporting::class;

    protected static ?string $slug = 'reporting/monthly/laporan-detail';

    protected static ?string $navigationLabel = 'Laporan Detail';

    protected static ?string $title = 'Laporan Detail';

    protected ?string $heading = 'Laporan Detail';

    public function getTableRecordKey($record): string
    {
        return (string) $record->getKeyName();
    }
    protected static string $view = 'filament.pages.report-detail';

    protected static ?string $navigationGroup = 'Reports';

    public static function table(Table $table): Table
    {
        // $currentMonth = Carbon::now()->format('m');
        // $currentYear = Carbon::now()->format('Y');

        return $table
            ->query(Reporting::query()
                    ->join('outstandings', 'outstandings.id', '=', 'reportings.outstanding_id')
                    ->orderBy('outstandings.date_in', 'asc')
                    ->orderBy('outstandings.date_visit', 'asc')
                    ->select('reportings.*')
                )
            // ->defaultSort('outstanding.date_in', 'asc')
            // ->defaultSort('date_visit', 'asc')
            ->columns([
                TextColumn::make('outstanding.number')
                    ->label('No. Tiket')
                    ->limit('13')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('outstanding.location.name')
                    ->label('Lokasi')
                    ->limit('10'),
                TextColumn::make('outstanding.product.name')
                    ->label('Produk')
                    ->limit('15')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('outstanding.reporter')
                    ->label('Pelapor')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                TextColumn::make('outstanding.title')
                    ->label('Masalah'),
                TextColumn::make('outstanding.date_in')
                    ->label('Lapor')
                    ->date(),
                TextColumn::make('date_visit')
                    ->label('Aksi')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('outstanding.date_finish')
                    ->label('Selesai')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('users.firstname')
                    ->label('Support'),
                TextColumn::make('work')
                    ->label('Tipe')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                TextColumn::make('cause')
                    ->label('Sebab')
                    ->html(),
                TextColumn::make('action')
                    ->label('Aksi')
                    ->html(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('outstanding.is_type_problem')
                    ->label('Tipe Problem')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->badge(),
                TextColumn::make('outstanding.outstandingunits.unit.name')
                    ->label('Unit')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->badge(),
                TextColumn::make('note')
                    ->label('Ket.')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->html(),
            ])
            ->persistSortInSession()
            ->persistFiltersInSession()
            // ->defaultSort('number', 'asc')
            ->filtersFormColumns(4)
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        SelectConstraint::make('outstanding.location.name')
                            ->label('Lokasi')
                            ->icon('heroicon-m-map-pin')
                            ->options(Location::all()->pluck('name', 'name'))
                            ->multiple()
                            ->searchable(),
                        SelectConstraint::make('outstanding.location.team.name')
                            ->label('Team')
                            ->icon('heroicon-m-rectangle-group')
                            ->options(Team::all()->pluck('name', 'name'))
                            ->multiple()
                            ->searchable(),
                        SelectConstraint::make('outstanding.product.name')
                            ->label('Produk')
                            ->icon('heroicon-m-star')
                            ->options(Product::all()->pluck('name', 'name'))
                            ->multiple()
                            ->searchable(),
                        SelectConstraint::make('users.firstname')
                            ->label('Support')
                            ->icon('heroicon-m-users')
                            ->options(User::all()->pluck('firstname', 'firstname'))
                            ->multiple()
                            ->searchable(),
                        SelectConstraint::make('outstanding.reporter')
                            ->label('Pelapor')
                            ->options([
                                'client' => 'Client',
                                'preventif' => 'Preventif',
                                'support' => 'Support',
                            ])
                            ->icon('heroicon-m-chat-bubble-left')
                            ->multiple(),
                        DateConstraint::make('outstanding.date_in')
                            ->label('Tanggal Lapor')
                            ->icon('heroicon-m-calendar')
                    ]),
            ], layout: FiltersLayout::Modal)
            ->filtersFormWidth(MaxWidth::ExtraLarge)
            ->headerActions([
                ExportAction::make()
                    ->label('Export XLS/XLSX/CSV')
                    ->exports([
                        ExcelExport::make()
                            // ->queue()
                            ->withFilename('LaporanDetail-'.date('Ymd'))
                            ->askForWriterType()
                            ->fromTable()
                            ->withColumns([
                                Column::make('outstanding.number')->heading('No. Tiket'),
                                Column::make('outstanding.location.name')->heading('Lokasi'),
                                Column::make('outstanding.product.name')->heading('Produk'),
                                Column::make('outstanding.reporter')->heading('Pelapor')
                                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                                Column::make('outstanding.date_in')->heading('Lapor'),
                                Column::make('date_visit')->heading('Aksi'),
                                Column::make('outstanding.date_finish')->heading('Selesai'),
                                Column::make('outstanding.title')->heading('Masalah'),
                                Column::make('cause')->heading('Sebab'),
                                Column::make('action')->heading('Aksi')
                                    ->formatStateUsing(fn ($state) => strip_tags($state)),
                                Column::make('solution')->heading('Solusi')
                                    ->formatStateUsing(fn ($state) => strip_tags($state)),
                                Column::make('status')->heading('Status'),
                                Column::make('outstanding.is_type_problem')->heading('Tipe Problem'),
                                Column::make('outstanding.outstandingunits.unit.name')->heading('Unit'),
                                Column::make('note')->heading('Ket.')
                                    ->formatStateUsing(fn ($state) => strip_tags($state)),
                            ]),
                    ])
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
                // ExportBulkAction::make()
            ]);
    }

}
