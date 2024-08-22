<?php

namespace App\Filament\Widgets;

use App\Models\Reporting;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class ScheduleOutstandings extends BaseWidget
{
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $userTeam = $user ? $user->getTeamId() : null ;
        $isHead = $user && $user->hasRole('head');

        $query = Reporting::query()
                    ->whereNull('reportings.status');

        if ($isHead) {
            $query->whereHas('users', function ($query) use ($userTeam) {
                $query->where('team_id', $userTeam);
            });
        }

        return $table
            ->defaultPaginationPageOption(5)
            ->query($query)
            ->defaultSort('reportings.date_visit', 'desc')
            ->poll('30s')
            ->deferLoading()
            ->columns([
                TextColumn::make('date_visit')
                    ->label('Tanggal')
                    ->date(),
                TextColumn::make('users.firstname')
                    ->label('Support'),
                TextColumn::make('outstanding.location.name_alias')
                    ->label('Lokasi')
                    ->limit(15),
                TextColumn::make('outstanding.title')
                    ->label('Masalah'),
            ]);
    }
}
