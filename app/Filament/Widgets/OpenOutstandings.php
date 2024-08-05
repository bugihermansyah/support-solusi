<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OutstandingResource;
use App\Models\Outstanding;
use App\Tables\Columns\SlaFinishColumn;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class OpenOutstandings extends BaseWidget
{
    // public function getTableRecordKey($record): string
    // {
    //     return (string) $record->id;
    // }

    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $userTeam = $user ? $user->getTeamId() : null ;
        $isHead = $user && $user->hasRole('head');

        $query = Outstanding::query()
                    ->where('outstandings.status', 0)
                    ->orderByRaw("
                    CASE
                        WHEN priority = 'high' THEN 1
                        WHEN priority = 'normal' THEN 2
                        WHEN priority = 'low' THEN 3
                        ELSE 4
                    END
                    ");

        if ($isHead) {
                $query->join('locations', 'locations.id', '=', 'outstandings.location_id')
                      ->where('locations.team_id', $userTeam);
        }
        return $table
            ->defaultPaginationPageOption(5)
            ->query($query->select('outstandings.*'))
            ->defaultSort('date_in', 'asc')
            // ->defaultSort('priority', 'asc')
            ->poll('30s')
            ->deferLoading()
            ->columns([
                TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->limit(15)
                    ->badge()
                    ->color(function ($record): string {
                        $createdAt = Carbon::parse($record->date_in);
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
                        $createdAt = Carbon::parse($record->date_in);
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
                IconColumn::make('temporary')
                    ->label('Temp')
                    ->getStateUsing(function (Outstanding $record){
                        return $record->date_finish ? true : false;
                    })
                    ->boolean(),
            ])
            ->actions([
                Action::make('edit')
                    ->icon('heroicon-m-eye')
                    ->hiddenLabel()
                    ->url(fn (Outstanding $record): string => route('filament.admin.resources.outstandings.edit', $record->id))
                    ->openUrlInNewTab(),
            ]);
    }
}
