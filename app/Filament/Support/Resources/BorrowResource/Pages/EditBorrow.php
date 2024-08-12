<?php

namespace App\Filament\Support\Resources\BorrowResource\Pages;

use App\Filament\Resources\Warehouse\LoanResource;
use App\Filament\Support\Resources\BorrowResource;
use App\Models\Loan;
use App\Models\Unit;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Actions\Action as ActionsAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Model;

class EditBorrow extends EditRecord
{
    protected static string $resource = BorrowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('returnUnit')
                ->disabled(fn (Model $record) => $record->completed_at)
                ->form(function (Loan $record) {
                    $borrowedUnits = $record->loanUnits()->pluck('unit_id')->toArray();
                    $unitOptions = Unit::whereIn('id', $borrowedUnits)
                                    ->where('is_warehouse', 1)
                                    ->pluck('name', 'id');

                    return [
                        TableRepeater::make('returnUnits')
                            ->label('Pengembalian Unit')
                            ->addActionLabel('Tambah unit')
                            ->cloneable()
                            ->minItems(1)
                            ->schema([
                                Forms\Components\Select::make('unit_id')
                                    ->label('Unit')
                                    ->options($unitOptions)
                                    ->searchable()
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                Forms\Components\TextInput::make('remark')
                                    ->label('Remark'),
                                Forms\Components\TextInput::make('qty')
                                    ->numeric()
                                    ->integer()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1),
                            ])
                            ->colStyles([
                                'unit_id' => 'width: 1000px;',
                                'remark' => 'width: 300px',
                                'qty' => 'width: 70px;',
                            ])
                            ->reorderable()
                            ->collapsible(),
                    ];
                })
                ->action(function (array $data, Loan $record): void {
                        foreach ($data['returnUnits'] as $returnUnitData) {
                            $record->returnUnits()->create([
                                'unit_id' => $returnUnitData['unit_id'],
                                'qty' => $returnUnitData['qty'],
                                'remark' => $returnUnitData['remark'] ?? null,
                            ]);
                        }

                    $record->save();

                    $user = auth()->user();
                    $userAdmin = User::where('email', 'support@ptsap.co.id')->first();

                    if ($userAdmin) {
                        Notification::make()
                            ->title("{$user->firstname} {$user->lastname}")
                            ->icon('heroicon-o-cpu-chip')
                            ->body("Pengembalian dengan No. <b>{$record->number}</b>")
                            ->actions([
                                ActionsAction::make('Lihat')
                                    ->url(LoanResource::getUrl('edit', ['record' => $record], panel: 'admin')),
                            ])
                            ->sendToDatabase($userAdmin);
                    }

                })
        ];
    }
}
