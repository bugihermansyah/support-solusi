<?php

namespace App\Filament\Support\Resources\BorrowResource\Pages;

use App\Filament\Support\Resources\BorrowResource;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ManageReturn extends ManageRelatedRecords
{
    protected static string $resource = BorrowResource::class;

    protected static string $relationship = 'returnUnits';

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    public function getTitle(): string | Htmlable
    {
        return "Status pengembalian unit";
    }

    public function getBreadcrumb(): string
    {
        return 'Pengembalian';
    }

    public static function getNavigationLabel(): string
    {
        return 'Pengembalian unit';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('unit_id')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('unit.name')
                    ->label('Unit'),
                Tables\Columns\TextColumn::make('remark')
                    ->label('Remark'),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->accepted_at) {
                            return 'Diterima';
                        } elseif ($record->rejected_at) {
                            return 'Ditolak';
                        } else {
                            return 'Pending';
                        }
                    })
                    ->icons([
                        'heroicon-o-x-circle' => fn ($state): bool => $state === 'Ditolak',
                        'heroicon-o-check-circle' => fn ($state): bool => $state === 'Diterima',
                    ])
                    ->colors([
                        'danger' => 'Ditolak',
                        'success' => 'Diterima',
                        'secondary' => 'Pending',
                    ]),
                Tables\Columns\TextColumn::make('comment')
                    ->label('Catatan'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
