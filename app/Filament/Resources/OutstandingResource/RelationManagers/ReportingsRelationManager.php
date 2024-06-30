<?php

namespace App\Filament\Resources\OutstandingResource\RelationManagers;

use App\Models\Team;
use Carbon\Carbon;
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

    protected static ?string $title = 'Laporan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\DatePicker::make('date_visit')
                            ->label('Tanggal Aksi')
                            ->default(Carbon::now())
                            ->native(false)
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->label('Support')
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
                        Forms\Components\ToggleButtons::make('work')
                            ->label('Jenis Aksi')
                            ->inline()
                            ->options([
                                'visit' => 'Visit',
                                'remote' => 'Remote'
                            ])
                            ->colors([
                                'visit' => 'info',
                                'remote' => 'warning',
                            ])
                            ->default('visit')
                            ->grouped()
                            ->required(),
                        Forms\Components\ToggleButtons::make('status')
                            ->inline()
                            ->options([
                                '1' => 'Finish',
                                '0' => 'Pending',
                            ])
                            ->icons([
                                '1' => 'heroicon-o-check',
                                '0' => 'heroicon-o-x-mark',
                            ])
                            ->colors([
                                '1' => 'success',
                                '0' => 'warning',
                            ])
                            ->default('1')
                            ->grouped()
                            ->required(),
                        Forms\Components\RichEditor::make('note')
                            ->label('Keterangan')
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'italic',
                                'orderedList',
                            ])
                            ->extraInputAttributes([
                                'style' => 'min-height: 100px;',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(),

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
                Tables\Columns\TextColumn::make('date_visit')
                    ->label('Tanggal')
                    ->date(),
                Tables\Columns\TextColumn::make('user.firstname')
                    ->label('Support'),
                Tables\Columns\TextColumn::make('work')
                    ->label('Tipe Aksi'),
                Tables\Columns\TextColumn::make('cause')
                    ->label('Sebab')
                    ->html(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Aksi')
                    ->html(),
                Tables\Columns\TextColumn::make('solution')
                    ->label('Solusi')
                    ->html(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
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
