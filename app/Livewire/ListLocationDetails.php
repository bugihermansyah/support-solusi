<?php

namespace App\Livewire;

use App\Filament\Clusters\ProblematicLocations;
use App\Models\Location;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class ListLocationDetails extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    // protected static ?string $navigationLabel = 'list problem';
    // protected static ?string $cluster = ProblematicLocations::class;
    public function getTableRecordKey($record): string
    {
        return (string) $record->getKeyName();
    }

    /**
     * @var ?string
     */
    #[Url]
    public $tableSearch = '';

    public function table(Table $table): Table
    {
        return $table
        ->query(Location::query()
                ->join('outstandings as o', 'locations.id', '=', 'o.location_id')
                ->select('name', DB::raw('COUNT(o.id) as outstanding_count'))
                ->whereMonth('o.created_at', 9)
                ->whereYear('o.created_at', 2024)
                ->groupBy('name')
                ->having(DB::raw('COUNT(o.id)'), '>', 2)
                ->orderByDesc('outstanding_count')
                ->when($this->tableSearch, function ($query) {
                    $query->where('locations.name', 'like', '%' . $this->tableSearch . '%');
                })
            )
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
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }

    public function render()
    {
        return view('livewire.list-location-details');
    }
}
