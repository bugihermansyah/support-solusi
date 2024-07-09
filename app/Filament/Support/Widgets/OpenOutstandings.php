<?php

namespace App\Filament\Support\Widgets;

use App\Filament\Support\Resources\OutstandingResource;
use App\Models\Outstanding;
use Carbon\Carbon;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class OpenOutstandings extends BaseWidget
{
    protected static ?int $sort = 2;

    // protected static string $relationship = 'reportings';

    public function table(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->defaultPaginationPageOption(5)
            ->query(
                Outstanding::query()
                    ->where('user_id', $user->id)
                    ->where('status', 0)
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
                        // ->grow(false)
                        ->searchable(),
                    Tables\Columns\TextColumn::make('date_in')
                        ->label('Lapor')
                        ->icon('heroicon-m-clock')
                        ->formatStateUsing(function ($state) {
                            $createdDate = Carbon::parse($state);
                            $daysDifference = $createdDate->subDays(1)->longAbsoluteDiffForHumans();
                            return $daysDifference;
                        }),
                    Tables\Columns\TextColumn::make('reportings_count')
                        ->label('Aksi')
                        ->icon('heroicon-m-wrench-screwdriver')
                        ->visibleFrom('md')
                        ->prefix('x')
                        ->counts('reportings'),
                    ])->from('md'),
            ])
            // ->defaultSort('title', 'asc')
            ->actions([
                Tables\Actions\Action::make('Buka')
                    ->url(fn (Outstanding $record): string => OutstandingResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
