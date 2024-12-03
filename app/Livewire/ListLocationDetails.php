<?php

namespace App\Livewire;

use App\Models\Location;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class ListLocationDetails extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function getTableRecordKey($record): string
    {
        return (string) $record->getKeyName();
    }

    /**
     * @var ?string
     */
    #[Url]
    public $tableSearch = '';

    /**
     * @var array<string, mixed> | null
     */
    #[Url]
    public ?array $tableFilters = null;

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $userTeam = $user ? $user->getTeamId() : null ;

        return $table
            ->query(Location::query()
                ->join('outstandings as o', 'locations.id', '=', 'o.location_id')
                ->select('name', DB::raw('COUNT(o.id) as outstanding_count'))
                    ->when($this->tableSearch, function ($query) {
                        $query->where('locations.name', 'like', '%' . $this->tableSearch . '%');
                    })
                    // ->when(!empty($this->tableFilters['month']), function ($query) {
                    //     $query->whereMonth('o.date_in', $this->tableFilters['month']);
                    // })
                    // ->when(!empty($this->tableFilters['year']), function ($query) {
                    //     $query->whereYear('o.date_in', $this->tableFilters['year']);
                    // })
                ->where('team_id', $userTeam)
                ->whereIn('reporter', ['client', 'support'])
                ->groupBy('name')
                ->having(DB::raw('COUNT(o.id)'), '>', 2)
                ->orderByDesc('outstanding_count')
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
                Filter::make('month')
                    ->form([
                        Select::make('values')
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
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['values'],
                                fn (Builder $query, $month): Builder => $query->whereMonth('o.date_in', '=', $month),
                            );
                    }),
                    Filter::make('year')
                        ->form([
                            Select::make('values')
                                ->label('Year')
                                ->options([
                                    '2024' => '2024',
                                    '2023' => '2023',
                                    '2022' => '2022',
                                ]),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when(
                                    $data['values'],
                                    fn (Builder $query, $year): Builder => $query->whereYear('o.date_in', '=', $year),
                                );
                        }),
            ])
            ->deferFilters()
            ->actions([
                Action::make('search_by_location')
                ->label('Search by this location')
                ->url(function ($record) {
                    $filters = request()->query('tableFilters', []);

                    $urlParams = [
                        'tableSearch' => $record->name,
                    ];

                    if (isset($filters['month']['values']) && $filters['month']['values'] !== null) {
                        $urlParams['tableFilters[month][values]'] = $filters['month']['values']; // Include month filter
                    }

                    if (isset($filters['year']['values']) && $filters['year']['values'] !== null) {
                        $urlParams['tableFilters[year][values]'] = $filters['year']['values']; // Include year filter
                    }

                    return '?' . http_build_query($urlParams);
                })
                ->color('primary')
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
