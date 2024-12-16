<?php

namespace App\Filament\Pages;

use App\Models\Company;
use App\Models\Location;
use App\Models\Product;
use App\Models\Reporting;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class AdminReportDetail extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $model = Reporting::class;

    protected static ?string $slug = 'reporting/monthly/admin/detail-report';

    protected static ?string $navigationLabel = 'Report Detail';

    protected static ?string $title = 'Admin Report Detail';

    protected ?string $heading = 'Report Detail';

    public function getTableRecordKey($record): string
    {
        return (string) $record->getKeyName();
    }
    protected static string $view = 'filament.pages.report-detail';

    protected static ?string $navigationGroup = 'Reports';

    public static function table(Table $table): Table
    {
        return $table
            ->query(Reporting::query()
                    ->join('outstandings', 'outstandings.id', '=', 'reportings.outstanding_id')
                    ->orderBy('outstandings.date_in', 'asc')
                    ->orderBy('outstandings.date_visit', 'asc')
                    ->select('reportings.*')
                )
            ->columns([
                TextColumn::make('outstanding.number')
                    ->label('No. Tiket')
                    ->limit('13')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('outstanding.location.company.alias')
                    ->label('Group')
                    ->limit('13')
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
                TextColumn::make('outstanding.date_in')
                    ->label('Lapor')
                    ->date(),
                TextColumn::make('outstanding.date_visit')
                    ->label('SLA Visit')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('outstanding.date_finish')
                    ->label('SLA Selesai')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('users.firstname')
                    ->label('Support'),
                TextColumn::make('work')
                    ->label('Tipe')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                TextColumn::make('date_visit')
                    ->label('Visit')
                    ->date(),
                TextColumn::make('outstanding.title')
                    ->label('Masalah'),
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
            ->filtersFormColumns(2)
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Group')
                    ->multiple()
                    ->options(Company::all()->pluck('alias', 'id')),
                SelectFilter::make('location_id')
                    ->label('Loation')
                    ->multiple()
                    ->options(Location::all()->pluck('name_alias', 'id')),
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->multiple()
                    ->options(Product::all()->pluck('name', 'id')),
                SelectFilter::make('user_id')
                    ->label('Support')
                    ->multiple()
                    ->options(User::role(['head', 'staff', 'admin'])->pluck('firstname', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (!isset($data['values']) || empty($data['values'])) {
                            return $query; 
                        }
                        return $query->whereHas('users', function (Builder $query) use ($data) {
                            $query->whereIn('users.id', $data['values']);
                        });
                    }),
                SelectFilter::make('reporter')
                    ->label('Reporter')
                    ->multiple()
                    ->options([
                        'client' => 'Client',
                        'preventif' => 'Preventive',
                        'support' => 'Internal'
                    ]),
                Filter::make('report_date')
                    ->form([
                        DatePicker::make('reported_from')
                            ->label('Report From'),
                        DatePicker::make('reported_until')
                            ->label('Report Until')
                            ->default(now()),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                 
                        if ($data['reported_from'] ?? null) {
                            $indicators[] = Indicator::make('Reported from ' . Carbon::parse($data['reported_from'])->toFormattedDateString())
                                ->removeField('reported_from');
                        }
                 
                        if ($data['reported_until'] ?? null) {
                            $indicators[] = Indicator::make('Reported until ' . Carbon::parse($data['reported_until'])->toFormattedDateString())
                                ->removeField('reported_until');
                        }
                 
                        return $indicators;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['reported_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('outstandings.date_in', '>=', $date),
                            )
                            ->when(
                                $data['reported_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('outstandings.date_in', '<=', $date),
                            );
                    }),
                TernaryFilter::make('lpm'),
                TernaryFilter::make('is_implement')
                    ->label('Implement'),
                TernaryFilter::make('is_oncall')
                    ->label('On Call'),
                SelectFilter::make('sla_visit')
                    ->options([
                        'sla1' => 'SLA 1',
                        'sla2' => 'SLA 2',
                        'sla3' => 'SLA 3',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function (Builder $query, $value) {
                            switch ($value) {
                                case 'sla1':
                                    return $query->whereRaw('DATEDIFF(outstandings.date_visit, outstandings.date_in) <= 1');
                                case 'sla2':
                                    return $query->whereRaw('DATEDIFF(outstandings.date_visit, outstandings.date_in) BETWEEN 2 AND 3');
                                case 'sla3':
                                    return $query->whereRaw('DATEDIFF(outstandings.date_visit, outstandings.date_in) > 3');
                            }
                        });
                    })
                    ->label('SLA Visit'),
                SelectFilter::make('sla_finish')
                    ->options([
                        'sla1' => 'SLA 1',
                        'sla2' => 'SLA 2',
                        'sla3' => 'SLA 3',
                        'sla4' => 'null',
                    ])
                    ->default('')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function (Builder $query, $value) {
                            switch ($value) {
                                case 'sla1':
                                    return $query->whereRaw('DATEDIFF(outstandings.date_finish, outstandings.date_in) <= 3');
                                case 'sla2':
                                    return $query->whereRaw('DATEDIFF(outstandings.date_finish, outstandings.date_in) BETWEEN 4 AND 7');
                                case 'sla3':
                                    return $query->whereRaw('DATEDIFF(outstandings.date_finish, outstandings.date_in) > 7');
                                case 'sla4':
                                    return $query->whereNull('outstandings.date_finish');
                            }
                        });
                    })
                    ->label('SLA Finish'),
                SelectFilter::make('work')
                    ->options([
                        'visit' => 'Visit',
                        'remote' => 'Remote'
                    ]),
                Filter::make('date_visit')
                    ->form([
                        DatePicker::make('visited_from')
                            ->label('Visit From'),
                        DatePicker::make('visited_until')
                            ->label('Visit Until')
                            ->default(now()),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                 
                        if ($data['visited_from'] ?? null) {
                            $indicators[] = Indicator::make('Visited from ' . Carbon::parse($data['visited_from'])->toFormattedDateString())
                                ->removeField('visited_from');
                        }
                 
                        if ($data['visited_until'] ?? null) {
                            $indicators[] = Indicator::make('Visited until ' . Carbon::parse($data['visited_until'])->toFormattedDateString())
                                ->removeField('visited_until');
                        }
                 
                        return $indicators;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['visited_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('reportings.date_visit', '>=', $date),
                            )
                            ->when(
                                $data['visited_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('reportings.date_visit', '<=', $date),
                            );
                    }),
            ], layout: FiltersLayout::Modal)
            ->filtersFormSchema(fn (array $filters): array => [
                Fieldset::make('Outstanding')
                    ->schema([
                        $filters['company_id'],
                        $filters['location_id'],
                        $filters['product_id'],
                        $filters['reporter'],
                        $filters['sla_visit'],
                        $filters['sla_finish'],
                        $filters['report_date'],
                    ])
                    ->inlineLabel()
                    ->columns(2)
                    ->columnSpanFull(),
                Fieldset::make('Status')
                    ->schema([
                        $filters['lpm'],
                        $filters['is_implement'],
                        $filters['is_oncall'],
                    ])
                    // ->inlineLabel()
                    ->columns(3)
                    ->columnSpanFull(),
                Fieldset::make('Reporting')
                    ->schema([
                        $filters['user_id'],
                        $filters['work'],
                        $filters['date_visit'],
                    ])
                    ->inlineLabel()
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->deferFilters()
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            // ->filtersFormWidth(MaxWidth::ExtraLarge)
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
                                // Column::make('outstanding.date_in')
                                //     ->heading('Lapor'),
                                // Column::make('outstanding.date_visit')->heading('Aksi'),
                                // Column::make('outstanding.date_finish')->heading('Selesai'),
                                Column::make('outstanding.title')->heading('Masalah'),
                                Column::make('cause')->heading('Sebab'),
                                Column::make('action')->heading('Aksi')
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
                //
            ])
            ->bulkActions([
                //
            ]);
    }

}
