<?php

namespace App\Filament\Pages\Widgets;

use App\Models\Outstanding;
use App\Models\Reporting;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OpenOutstandings extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();

        // Subquery untuk mendapatkan reporting terakhir per outstanding
        $latest = Reporting::selectRaw('MAX(id) as id')
            ->whereNotNull('outstanding_id')
            ->groupBy('outstanding_id');

        // Query utama
        $query = Reporting::query()
            ->joinSub($latest, 'latest', fn($join) => $join->on('reportings.id', '=', 'latest.id'))
            ->join('outstandings', 'outstandings.id', '=', 'reportings.outstanding_id')
            ->where('outstandings.status', 0)
            ->where('outstandings.is_implement', 0)
            ->where('outstandings.reporter', '!=', 'preventif')
            ->where('outstandings.date_in', '<=', now()->subDays(3))
            ->when($user->team_id && !$user->hasRole('admin'), function ($q) use ($user) {
                $q->join('locations', 'locations.id', '=', 'outstandings.location_id')
                  ->where('locations.team_id', $user->team_id);
            })
            ->with(['outstanding.location'])
            ->select('reportings.*');

        return $table
            ->query($query)
            ->poll(10)
            ->columns([
                TextColumn::make('outstanding.location.name')
                    ->label('Location')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('outstanding.title')
                    ->label('Outstanding')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('outstanding.date_in')
                    ->label('Report client')
                    ->sortable()
                    ->date('d M Y'),
                TextColumn::make('since')
                    ->label('Since (Days)')
                    ->state(function ($record) {
                        if (! $record->outstanding?->date_in) {
                            return '-';
                        }

                        return now()->diffInDays($record->outstanding->date_in);
                    })
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('outstandings.date_in', $direction);
                    })
                    ->color(fn ($state) => match (true) {
                        $state >= 7 => 'danger',
                        $state >= 3 => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->badge(),
                TextColumn::make('revisit')
                    ->label('Revisit')
                    ->sortable()
                    ->date('d M Y'),
                TextColumn::make('revisit_diff')
                    ->label('Revisit In')
                    ->state(function ($record) {
                        if (! $record->revisit) {
                            return '-';
                        }

                        return now()->diffInDays($record->revisit, false); // false = arah positif/negatif
                    })
                    ->badge()
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('reportings.revisit', $direction);
                    })
                    ->color(fn ($state) => match (true) {
                        $state < 0 => 'danger',      // sudah lewat
                        $state == 0 => 'warning',    // hari ini
                        $state <= 3 => 'info',       // mendekati
                        default => 'success',        // masih lama
                    })
                    ->formatStateUsing(fn ($state) => is_numeric($state)
                        ? ($state >= 0 ? "{$state} days left" : abs($state) . ' days ago')
                        : $state),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->recordUrl(
                fn ($record) => route('filament.admin.resources.outstandings.edit', [
                    'record' => $record->outstanding_id, // pastikan ini id yang benar
                ])
            )
            ->filters([
                SelectFilter::make('reportings.status')
                    ->label('Status')
                    ->options([
                        0 => 'Pending SAP',
                        2 => 'Pending Client',
                        3 => 'Temporary',
                        4 => 'Monitoring',
                    ]),
            ]);
    }
}
