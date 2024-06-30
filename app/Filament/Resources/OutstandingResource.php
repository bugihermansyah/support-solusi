<?php

namespace App\Filament\Resources;

use App\Enums\OutstandingStatus;
use App\Filament\Resources\OutstandingResource\Pages;
use App\Filament\Resources\OutstandingResource\RelationManagers;
use App\Models\Contract;
use App\Models\Location;
use App\Models\Outstanding;
use App\Models\Product;
use App\Models\Team;
use App\Models\Unit;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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

    protected static ?string $modelLabel = 'Outstanding';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

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
                                ->options(function (callable $get){
                                    $product = Location::find($get('location_id'));

                                    if (!$product) {
                                        return Product::all()->pluck('name', 'id');
                                    }
                                    if ($product->contracts) {
                                        return $product->contracts->pluck('product.name', 'product.id');
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
                                ->required()
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('number')
                                ->label('No. Tiket')
                                ->default('SP-' .Carbon::now()->format('ym').''.(random_int(100000, 999999)))
                                ->disabled()
                                ->dehydrated()
                                ->required()
                                ->maxLength(32)
                                ->unique(Outstanding::class, 'number', ignoreRecord: true),
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
                    Group::make()
                        ->schema([

                            Forms\Components\Section::make('Gambar')
                                ->schema([
                                    SpatieMediaLibraryFileUpload::make('image')
                                        ->imageEditor()
                                        ->resize(20)
                                        ->openable(),
                                ])
                                ->columnSpan(2)
                                ->collapsed(),
                        ])
                        ->columns(4)
                ])
                ->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Unit')
                        ->schema([
                            TableRepeater::make('outstandingunits')
                                ->label('')
                                ->collapsible()
                                ->relationship()
                                ->headers([
                                    Header::make('nama')->width('200px'),
                                    Header::make('qty')->width('50px'),
                                ])
                                // ->renderHeader(false)
                                ->streamlined()
                                ->schema([
                                    Forms\Components\Select::make('unit_id')
                                        ->label('Unit')
                                        ->options(Unit::where('is_visible', 1)->pluck('name', 'id'))
                                        ->placeholder('Pilih unit')
                                        ->searchable()
                                        ->required(),
                                        // ->columnSpan([
                                        //     'md' => 7,
                                        // ]),

                                    Forms\Components\TextInput::make('qty')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(20)
                                        ->default(1)
                                        ->required(),
                                        // ->columnSpan([
                                        //     'md' => 3,
                                        // ]),
                                ])
                            ->defaultItems(0),
                            // ->columns([
                            //     'md' => 10,
                            // ]),
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
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('number')
                ->label('No. Tiket')
                ->searchable(),
            Tables\Columns\TextColumn::make('location.team.name')
                ->label('Tim Area')
                ->searchable()
                ->sortable()
                ->limit(5),
            Tables\Columns\TextColumn::make('location.name')
                ->label('Lokasi')
                ->searchable()
                ->sortable()
                ->limit(15),
            Tables\Columns\TextColumn::make('product.name')
                ->label('Produk')
                ->searchable()
                ->sortable()
                ->limit(15),
            Tables\Columns\TextColumn::make('title')
                ->label('Masalah')
                ->limit(25)
                ->searchable(),
            Tables\Columns\TextColumn::make('reporter')
                ->label('Pelapor')
                // ->formatStateUsing(fn ($state) => Str::headline($state))
                ->formatStateUsing(fn ($state) => ucwords($state))
                ->colors([
                    'danger' => 'client',
                    'warning' => 'preventif',
                    'success' => 'support',
                ])
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('date_in')
                ->label('Lapor')
                ->date()
                ->sortable(),
            // Tables\Columns\TextColumn::make('date_visit')
            //     ->label('Visit date')
            //     ->date(),
            // Tables\Columns\TextColumn::make('date_finish')
            //     ->label('Finish date')
            //     ->date()
            //     ->sortable(),
            Tables\Columns\TextColumn::make('reportings_count')
                ->label('Aksi')
                ->suffix('x')
                ->sortable()
                ->counts('reportings'),
            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->searchable()
                ->sortable(),
            ])
            ->filters([
                //
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
                    ->label('Tanggal visit/Remote')
                    ->native(false)
                    ->default(Carbon::now())
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
                Forms\Components\Select::make('work')
                    ->label('Visit/Remote')
                    ->options([
                        'visit' => 'Visit',
                        'remote' => 'Remote',
                    ])
                    ->default('visit')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        '0' => 'Pending',
                        '1' => 'Finish',
                    ])
                    ->default('1')
                    ->required(),
                Forms\Components\RichEditor::make('cause')
                    ->label('Sebab')
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'italic',
                        'orderedList',
                        'underline',
                    ])
                    ->extraInputAttributes([
                        'style' => 'min-height: 100px;',
                    ]),
                Forms\Components\RichEditor::make('action')
                    ->label('Aksi')
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'italic',
                        'orderedList',
                        'underline',
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
                        'underline',
                    ])
                    ->extraInputAttributes([
                        'style' => 'min-height: 100px;',
                    ]),
                Forms\Components\RichEditor::make('note')
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'italic',
                        'orderedList',
                        'underline',
                    ])
                    ->extraInputAttributes([
                        'style' => 'min-height: 100px;',
                    ]),
                ])
            ->visible(fn ($context) => $context === 'create')
            ->columns(4);
    }
}
