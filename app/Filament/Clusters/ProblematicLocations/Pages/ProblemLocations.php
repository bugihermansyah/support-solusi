<?php

namespace App\Filament\Clusters\ProblematicLocations\Pages;

use App\Filament\Clusters\ProblematicLocations;
use App\Models\Location;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

class ProblemLocations extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    #[Url(as:'cari', keep: true, history: true)]
    public $search = '';

    public function getTableRecordKey($record): string
    {
        return (string) $record->getKeyName();
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.clusters.problematic-locations.pages.problem-locations';

    protected static ?string $cluster = ProblematicLocations::class;

    public static function table(Table $table): Table
    {
        return $table
            ->query(Location::query()
                        ->join('outstandings as o', 'locations.id', '=', 'o.location_id')
                        ->select('name', DB::raw('COUNT(o.id) as outstanding_count'))
                        ->whereMonth('o.created_at', 9)
                        ->whereYear('o.created_at', 2024)
                        ->groupBy('name')
                        ->having(DB::raw('COUNT(o.id)'), '>', 2)
                        ->orderByDesc('outstanding_count'))
            ->columns([
                TextColumn::make('name')
                    ->label('Locations')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('outstanding_count')
                    ->label('Value')
                    ->sortable(),
            ])
            ->filters([
            ])
            ->headerActions([
                //
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
