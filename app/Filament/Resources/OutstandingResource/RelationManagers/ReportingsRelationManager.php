<?php

namespace App\Filament\Resources\OutstandingResource\RelationManagers;

use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReportingsRelationManager extends RelationManager
{
    protected static string $relationship = 'reportings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\DatePicker::make('date_visit')
                            ->native(false)
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->label('Staff')
                            ->options(function () {
                                $teams = Team::with('users')->get();
                                $options = [];
                                foreach ($teams as $team) {
                                    $teamUsers = $team->users->pluck('name', 'id')->toArray();
                                    $options[$team->name] = $teamUsers;
                                }
                                return $options;
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('work')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('status')
                            ->required()
                            ->maxLength(255),
                    ]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('cause')
                            ->label('Sebab')
                            ->required(),
                        Forms\Components\RichEditor::make('action')
                            ->label('Aksi')
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'italic',
                                'orderedList',
                            ])
                            ->extraInputAttributes([
                                'style' => 'min-height: 100px;',
                            ]),
                        Forms\Components\RichEditor::make('solution')
                            ->label('Solusi')
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'italic',
                                'orderedList',
                            ])
                            ->extraInputAttributes([
                                'style' => 'min-height: 100px;',
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('cause')
            ->columns([
                Tables\Columns\TextColumn::make('cause'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->hiddenLabel()->tooltip('Ubah'),
                Tables\Actions\DeleteAction::make()->hiddenLabel()->tooltip('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
