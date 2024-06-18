<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutstandingResource\Pages;
use App\Filament\Resources\OutstandingResource\RelationManagers;
use App\Models\Contract;
use App\Models\Location;
use App\Models\Outstanding;
use App\Models\Team;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class OutstandingResource extends Resource
{
    protected static ?string $model = Outstanding::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Select::make('location_id')
                                ->label('Lokasi')
                                ->options(Location::query()->pluck('name', 'id'))
                                ->live()
                                ->searchable()
                                ->required(),
                            Forms\Components\Select::make('product_id')
                                ->label('Produk')
                                ->options(function ($get): Collection {
                                    $locationId = $get('location_id');
                                    if ($locationId) {
                                        return Contract::query()
                                            ->where('location_id', $locationId)
                                            ->get()
                                            ->pluck('product.name', 'product.id');
                                    }
                                    return collect();
                                })
                                ->required(),
                            Forms\Components\Select::make('reporter')
                                ->label('Pelapor')
                                ->options([
                                    'client' => 'Client',
                                    'preventif' => 'Preventif',
                                    'support' => 'Support',
                                ])
                                ->default('client')
                                ->required(),
                            Forms\Components\TextInput::make('title')
                                ->label('Laporan masalah')
                                ->maxLength(100)
                                ->columnSpanFull()
                                ->required(),
                        ])
                        ->columns(3),

                    Forms\Components\Section::make('Informasi tanggal')
                        ->schema([
                            Forms\Components\DatePicker::make('date_in')
                                ->label('Awal')
                                ->default(now())
                                ->maxDate(now())
                                ->native(false)
                                ->required(),
                            Forms\Components\DatePicker::make('date_visit')
                                ->label('Visit/Remote')
                                ->native(false)
                                ->maxDate(now()),
                            Forms\Components\DatePicker::make('date_finish')
                                ->label('Selesai')
                                ->native(false)
                                ->maxDate(now()),
                        ])
                        ->collapsible()
                        ->columns(3),

                    Forms\Components\Section::make('Tipe masalah')
                        ->schema([
                            Forms\Components\ToggleButtons::make('is_type_problem')
                                ->label('')
                                ->options([
                                    '1' => 'Hardware',
                                    '2' => 'Software',
                                    '3' => 'H/W Non Unit',
                                    '4' => 'Sipil'
                                ])
                                ->inline()
                                ->inlineLabel(false),
                        ])
                        ->collapsible(),
                        // ->columns(3),

                    Forms\Components\Section::make('Informasi status')
                        ->schema([
                            Forms\Components\Checkbox::make('lpm')
                                ->label('Laporan Pertama Masuk'),
                            Forms\Components\Checkbox::make('is_implement')
                                ->label('Implementasi'),
                            Forms\Components\Checkbox::make('status')
                                ->label('Status Closed'),
                        ])
                        ->collapsible()
                        ->columns(3),
                ])
                ->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Unit')
                        ->schema([
                            Forms\Components\Repeater::make('outstandingunits')
                                ->label('')
                                ->collapsible()
                                ->relationship()
                                ->schema([
                                    Forms\Components\Select::make('unit_id')
                                        ->label('Unit')
                                        ->options(Unit::query()->pluck('name', 'id'))
                                        ->placeholder('Pilih unit')
                                        ->searchable()
                                        ->required()
                                        ->columnSpan([
                                            'md' => 7,
                                        ]),

                                    Forms\Components\TextInput::make('qty')
                                        ->numeric()
                                        ->default(1)
                                        ->required()
                                        ->columnSpan([
                                            'md' => 3,
                                        ]),
                                ])
                            ->defaultItems(0)
                            ->columns([
                                'md' => 10,
                            ]),
                        ]),
                ])
                ->columnSpan(['lg' => 1]),

            Forms\Components\Group::make()
                ->schema([
                    static::getItemsRepeater(),
                ])
                ->columnSpanFull(),
        ])
        ->columns(3);
            // ->schema([
            //     Forms\Components\Group::make()
            //         ->schema([
            //             Forms\Components\Section::make()
            //                 ->schema([
            //                     Forms\Components\Select::make('location_id')
            //                         ->label('Lokasi')
            //                         ->options(Location::query()->pluck('name', 'id'))
            //                         ->live()
            //                         ->searchable()
            //                         ->required(),
            //                     Forms\Components\Select::make('product_id')
            //                         ->label('Produk')
            //                         ->options(function ($get): Collection {
            //                             $locationId = $get('location_id');
            //                             if ($locationId) {
            //                                 return Contract::query()
            //                                     ->where('location_id', $locationId)
            //                                     ->get()
            //                                     ->pluck('product.name', 'product.id');
            //                             }
            //                             return collect();
            //                         })
            //                         ->required(),
            //                     Forms\Components\Select::make('reporter')
            //                         ->label('Pelapor')
            //                         ->options([
            //                             'Admin' => 'Admin',
            //                             'User' => 'User',
            //                         ])
            //                         ->required(),
            //                     Forms\Components\TextInput::make('title')
            //                         ->label('Judul')
            //                         ->maxLength(100)
            //                         ->columnSpanFull()
            //                         ->required(),
            //                 ])
            //             ->columns(3),
            //         ])
            //         ->columnSpan(['lg' => 2]),

            //     Forms\Components\Section::make()
            //         ->schema([
            //             static::getItemsRepeater(),
            //     ])
            //     ->columnSpanFull(),
            // ])
            // ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('location.name')
                ->searchable()
                ->sortable()
                ->limit(10),
            Tables\Columns\TextColumn::make('title')
                ->label('Problem')
                ->limit(25)
                ->searchable(),
            Tables\Columns\TextColumn::make('reporter')
                ->label('Reporter')
                // ->formatStateUsing(fn ($state) => Str::headline($state))
                ->colors([
                    'danger' => 'client',
                    'warning' => 'preventif',
                    'success' => 'support',
                ])
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('date_in')
                ->label('Reporting date')
                ->date()
                ->sortable(),
            Tables\Columns\TextColumn::make('date_visit')
                ->label('Visit date')
                ->date(),
            Tables\Columns\TextColumn::make('date_finish')
                ->label('Finish date')
                ->date()
                ->sortable(),
            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('reportings_count')
                ->label('Work')
                ->sortable()
                ->counts('reportings'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ReportingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOutstandings::route('/'),
            'create' => Pages\CreateOutstanding::route('/create'),
            'edit' => Pages\EditOutstanding::route('/{record}/edit'),
        ];
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('reportings')
            ->relationship()
            ->collapsible()
            ->schema([
                Forms\Components\DatePicker::make('date_visit')
                    ->label('Tanggal visit')
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
                    ->searchable(),
                Forms\Components\TextInput::make('work')
                    ->label('Work')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->label('Status')
                    ->required(),
                Forms\Components\RichEditor::make('cause')
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'italic',
                        'orderedList',
                        'underline',
                    ]),
                Forms\Components\RichEditor::make('action')
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'italic',
                        'orderedList',
                        'underline',
                    ]),
                Forms\Components\RichEditor::make('solution')
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'italic',
                        'orderedList',
                        'underline',
                    ]),
                Forms\Components\RichEditor::make('note')
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'italic',
                        'orderedList',
                        'underline',
                    ]),
                ])
            ->visible(fn ($context) => $context === 'create')
            ->columns(4);
    }
}
