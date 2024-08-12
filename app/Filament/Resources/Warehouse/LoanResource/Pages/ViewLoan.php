<?php

namespace App\Filament\Resources\Warehouse\LoanResource\Pages;

use App\Filament\Resources\Warehouse\LoanResource;
use Filament\Actions;
use App\Filament\Support\Resources\BorrowResource;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Actions\Action as ActionNotif;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewLoan extends ViewRecord
{
    protected static string $resource = LoanResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Action::make('process')
    //             ->hidden(fn(Model $record) => $record->processed_at)
    //             ->hidden(fn(Model $record)=> $record->rejected_at)
    //             ->color('warning')
    //             ->requiresConfirmation()
    //             ->action(function (Model $record): void {
    //                 $this->save();

    //                 $record->processed_at = Carbon::now();
    //                 $record->save();

    //                 $user = User::find($record->user_id);

    //                 if ($user) {
    //                     Notification::make()
    //                         ->title("{$record->number}")
    //                         ->icon('heroicon-o-cpu-chip')
    //                         ->body("Permintaan unit anda sedang di <b>proccess</b>")
    //                         ->actions([
    //                             ActionNotif::make('Lihat')
    //                                 ->url(BorrowResource::getUrl('edit', ['record' => $record], panel: 'support')),
    //                         ])
    //                         ->sendToDatabase($user);
    //                 }
    //             }),
    //         Action::make('approve')
    //             ->color('success')
    //             ->visible(fn(Model $record)=> $record->processed_at)
    //             ->hidden(fn(Model $record)=> $record->approved_at)
    //             ->requiresConfirmation()
    //             ->action(function (Model $record): void {
    //                 $record->approved_at = Carbon::now();
    //                 $record->save();

    //                 $user = User::find($record->user_id);

    //                 if ($user) {
    //                     Notification::make()
    //                         ->title("{$record->number}")
    //                         ->icon('heroicon-o-cpu-chip')
    //                         ->body("Permintaan unit anda di <b>Approve</b>")
    //                         ->actions([
    //                             ActionNotif::make('Lihat')
    //                                 ->url(BorrowResource::getUrl('edit', ['record' => $record], panel: 'support')),
    //                         ])
    //                         ->sendToDatabase($user);
    //                 }
    //             }),
    //         Action::make('reject')
    //             ->color('danger')
    //             ->visible(fn(Model $record)=> !$record->processed_at)
    //             ->visible(fn(Model $record)=> !$record->approved_at)
    //             ->hidden(fn(Model $record)=> $record->rejected_at)
    //             ->requiresConfirmation()
    //             ->action(function (Model $record): void {
    //                 $record->rejected_at = Carbon::now();
    //                 $record->completed_at = Carbon::now();
    //                 $record->save();

    //                 $user = User::find($record->user_id);

    //                 if ($user) {
    //                     Notification::make()
    //                         ->title("{$record->number}")
    //                         ->icon('heroicon-o-cpu-chip')
    //                         ->body("Permintaan unit anda di <b>Reject</b>")
    //                         ->actions([
    //                             ActionNotif::make('Lihat')
    //                                 ->url(BorrowResource::getUrl('edit', ['record' => $record], panel: 'support')),
    //                         ])
    //                         ->sendToDatabase($user);
    //                 }
    //             }),
    //         Action::make('done')
    //             ->color('success')
    //             ->visible(fn(Model $record)=> $record->processed_at)
    //             ->visible(fn(Model $record)=> $record->approved_at)
    //             ->hidden(fn(Model $record)=> $record->completed_at)
    //             ->requiresConfirmation()
    //             ->action(function (Model $record): void {
    //                 $record->completed_at = Carbon::now();
    //                 $record->save();

    //                 $user = User::find($record->user_id);

    //                 if ($user) {
    //                     Notification::make()
    //                         ->title("{$record->number}")
    //                         ->icon('heroicon-o-cpu-chip')
    //                         ->body("Permintaan unit anda <b>Completed</b>")
    //                         ->actions([
    //                             ActionNotif::make('Lihat')
    //                                 ->url(BorrowResource::getUrl('edit', ['record' => $record], panel: 'support')),
    //                         ])
    //                         ->sendToDatabase($user);
    //                 }
    //             }),
    //     ];
    // }
}
