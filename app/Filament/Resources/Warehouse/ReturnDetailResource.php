<?php

namespace App\Filament\Resources\Warehouse;

use App\Filament\Resources\Warehouse\ReturnDetailResource\Pages;
use App\Filament\Resources\Warehouse\ReturnDetailResource\RelationManagers;
use App\Filament\Support\Resources\BorrowResource;
use App\Models\Loan;
use App\Models\ReturnUnit;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Actions\Action as ActionsAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReturnDetailResource extends Resource
{
    protected static ?string $model = ReturnUnit::class;

    protected static ?string $modelLabel = 'Pengembalian';

    // protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Gudang';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->defaultSort('created_at', 'desc')
        ->columns([
            Tables\Columns\TextColumn::make('created_at')
                ->label('Tanggal')
                ->searchable()
                ->dateTime(),
            Tables\Columns\TextColumn::make('loan.number')
                ->label('No. Request')
                ->searchable(),
            Tables\Columns\TextColumn::make('unit.name')
                ->label('Nama Unit')
                ->searchable(),
            Tables\Columns\TextColumn::make('qty')
                ->label('Qty')
                ->searchable(),
            Tables\Columns\TextColumn::make('loan.user.name')
                ->label('Peminjam')
                ->searchable(),
            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->getStateUsing(function ($record) {
                    if ($record->rejected_at) {
                        return 'Ditolak';
                    }

                    if ($record->accepted_at) {
                        return 'Diterima';
                    }

                    return 'Pending';
                })
                ->icons([
                    'heroicon-o-x-circle' => fn ($state): bool => $state === 'Ditolak',
                    'heroicon-o-check-circle' => fn ($state): bool => $state === 'Diterima',
                ])
                ->colors([
                    'danger' => 'Ditolak',
                    'success' => 'Diterima',
                ]),
            Tables\Columns\TextColumn::make('comment')
                ->label('Catatan'),
        ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ActionsAction::make('accepted')
                        ->color('success')
                        ->icon('heroicon-o-check')
                        ->action(function (Model $record): void {
                            $record->accepted_at = Carbon::now();
                            $record->save();

                            $loan = Loan::find($record->loan_id);
                            $user = User::find($loan->user_id);

                            if ($user) {
                                Notification::make()
                                    ->title("{$loan->number}")
                                    ->icon('heroicon-o-cpu-chip')
                                    ->body("Pengembalian unit anda di <b>Accepted</b>")
                                    ->actions([
                                        Action::make('Lihat')
                                            ->url(BorrowResource::getUrl('edit', ['record' => $record], panel: 'support')),
                                    ])
                                    ->sendToDatabase($user);
                            }
                        }),
                    ActionsAction::make('rejected')
                        ->color('danger')
                        ->icon('heroicon-o-x-mark')
                        ->form([
                            Textarea::make('comment')
                                ->label('Catatan')
                                ->required()
                        ])
                        ->requiresConfirmation()
                        ->action(function (Model $record, array $data): void {
                            $record->rejected_at = Carbon::now();
                            $record->comment = $data['comment'];
                            $record->save();

                            $loan = Loan::find($record->loan_id);
                            $user = User::find($loan->user_id);

                            if ($user) {
                                Notification::make()
                                    ->title("{$loan->number}")
                                    ->icon('heroicon-o-x-mark')
                                    ->body("Pengembalian unit anda di <b>Rejected</b> dengan catatan: {$data['comment']}")
                                    ->actions([
                                        Action::make('Lihat')
                                            ->url(BorrowResource::getUrl('edit', ['record' => $record], panel: 'support')),
                                    ])
                                    ->sendToDatabase($user);
                            }
                        })
                ])
                ->visible(fn(Model $record)=> !$record->accepted_at && !$record->rejected_at)
                ->label('More actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size(ActionSize::ExtraSmall)
                ->color('primary')
                ->button()
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnDetails::route('/'),
            // 'create' => Pages\CreateReturnDetail::route('/create'),
            // 'edit' => Pages\EditReturnDetail::route('/{record}/edit'),
        ];
    }
}
