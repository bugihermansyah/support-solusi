<?php

namespace App\Filament\Support\Widgets;

use App\Filament\Support\Resources\OutstandingResource;
use App\Models\Outstanding;
use Carbon\Carbon;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OpenOutstandings extends BaseWidget
{
    protected static ?int $sort = 2;

    // protected static string $relationship = 'reportings';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        // $a = Outstanding::query()
        // ->whereHas('location', function (Builder $query) use ($user) {
        //     $query->where('location.user_id', $user->id)
        //         ->where('outstanding.status', 0);
        // });
        // dd($a);
        return $table
            ->defaultPaginationPageOption(5)
            ->query(
                Outstanding::query()
                    ->whereHas('location', function (Builder $query) use ($user) {
                        $query->where('locations.user_id', $user->id)
                            ->where('outstandings.status', 0);
                    })
            )
            ->columns([
                Split::make([
                    Tables\Columns\TextColumn::make('location.name')
                        ->label('Lokasi')
                        ->icon('heroicon-m-map-pin')
                        ->weight(FontWeight::Bold)
                        ->grow(false)
                        ->searchable(),
                    Tables\Columns\TextColumn::make('title')
                        ->label('Masalah')
                        ->icon('heroicon-m-inbox-arrow-down')
                        ->grow(false)
                        ->searchable(),
                    Tables\Columns\TextColumn::make('date_in')
                        ->label('Lapor')
                        ->icon('heroicon-m-clock')
                        ->visible('sm')
                        ->grow(false)
                        ->formatStateUsing(function ($state) {
                            $createdDate = Carbon::parse($state);
                            $daysDifference = $createdDate->subDays(0)->longAbsoluteDiffForHumans();
                            return $daysDifference;
                        }),
                        Stack::make([
                            Tables\Columns\TextColumn::make('date_in')
                                ->label('Lapor')
                                ->icon('heroicon-m-clock')
                                ->hidden('sm')
                                ->grow(false)
                                ->formatStateUsing(function ($state) {
                                    $createdDate = Carbon::parse($state);
                                    $daysDifference = $createdDate->subDays(0)->longAbsoluteDiffForHumans();
                                    return $daysDifference;
                                }),
                        ])
                        ->alignment(Alignment::End)
                        ->visible('md'),

                        Stack::make([
                            Tables\Columns\TextColumn::make('reportings_count')
                                ->label('Aksi')
                                ->icon('heroicon-m-wrench-screwdriver')
                                ->grow(false)
                                ->visibleFrom('md')
                                ->prefix('x')
                                ->counts('reportings'),
                        ])
                        ->alignment(Alignment::End)
                        ->visible('md'),
                    ])
                    ->from('md'),
            ])
            // ->defaultSort('title', 'asc')
            ->actions([
                Tables\Actions\Action::make('Buka')
                    ->url(fn (Outstanding $record): string => OutstandingResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
