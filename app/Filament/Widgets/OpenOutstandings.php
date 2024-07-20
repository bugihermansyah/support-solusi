<?php

namespace App\Filament\Widgets;

use App\Models\Outstanding;
use App\Tables\Columns\SlaFinishColumn;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class OpenOutstandings extends BaseWidget
{
    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $userTeam = $user ? $user->getTeamId() : null ;
        $isHead = $user && $user->hasRole('head');

        $query = Outstanding::query()
                    ->where('outstandings.status', 0);

        if ($isHead) {
                $query->join('locations', 'locations.id', '=', 'outstandings.location_id')
                      ->where('locations.team_id', $userTeam);
        }

        return $table
            ->defaultPaginationPageOption(5)
            ->query($query)
            ->defaultSort('date_in', 'asc')
            ->columns([
                TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->limit(15)
                    ->badge()
                    ->color(function ($record): string {
                        $createdAt = Carbon::parse($record->created_at);
                        $daysDifference = $createdAt->diffInDays(Carbon::now());

                        if ($daysDifference < 3) {
                            return 'gray';
                        } elseif ($daysDifference <= 7) {
                            return 'warning';
                        } else {
                            return 'danger';
                        }
                    }),
                TextColumn::make('title')
                    ->label('Masalah')
                    ->badge()
                    ->color(function ($record): string {
                        $createdAt = Carbon::parse($record->created_at);
                        $daysDifference = $createdAt->diffInDays(Carbon::now());

                        if ($daysDifference < 3) {
                            return 'gray';
                        } elseif ($daysDifference <= 7) {
                            return 'warning';
                        } else {
                            return 'danger';
                        }
                    }),
                TextColumn::make('date_in')
                    ->label('Sejak')
                    ->since()
                    ->badge()
                    ->color(function (string $state): string {
                        $createdAt = Carbon::parse($state);
                        $daysDifference = $createdAt->diffInDays(Carbon::now());

                        if ($daysDifference < 3) {
                            return 'gray';
                        } elseif ($daysDifference <= 7) {
                            return 'warning';
                        } else {
                            return 'danger';
                        }
                    }),
            ]);
    }
}
