<?php

namespace App\Filament\Resources\Warehouse\LoanResource\Pages;

use App\Filament\Resources\Warehouse\LoanResource;
use App\Filament\Support\Resources\BorrowResource;
use App\Models\Loan;
use App\Models\ReturnUnit;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Actions\Action as ActionsAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ManageReturn extends ManageRelatedRecords
{
    protected static string $resource = LoanResource::class;

    protected static string $relationship = 'returnUnits';

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    public function getTitle(): string | Htmlable
    {
        $recordTitle = $this->getRecordTitle();

        $recordTitle = $recordTitle instanceof Htmlable ? $recordTitle->toHtml() : $recordTitle;

        return "Manage {$recordTitle} returns";
    }

    public function getBreadcrumb(): string
    {
        return 'Returns';
    }

    public static function getNavigationLabel(): string
    {
        return 'Manage Returns';
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
                    ->label('Nama Unit'),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty'),
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
            ->headerActions([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('accepted')
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
                                        ActionsAction::make('Lihat')
                                            ->url(BorrowResource::getUrl('edit', ['record' => $record], panel: 'support')),
                                    ])
                                    ->sendToDatabase($user);
                            }
                        }),
                    Action::make('rejected')
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
                                        ActionsAction::make('Lihat')
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
                //     Tables\Actions\ForceDeleteBulkAction::make(),
                //     Tables\Actions\RestoreBulkAction::make(),
                // ]),
            ]);
    }
}
