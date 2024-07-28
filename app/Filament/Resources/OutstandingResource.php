<?php

namespace App\Filament\Resources;

use App\Enums\OutstandingPriority;
use App\Enums\OutstandingStatus;
use App\Filament\Resources\OutstandingResource\Pages;
use App\Filament\Resources\OutstandingResource\RelationManagers;
use App\Models\Contract;
use App\Models\Location;
use App\Models\Outstanding;
use App\Models\Product;
use App\Models\Reporting;
use App\Models\Team;
use App\Models\Unit;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class OutstandingResource extends Resource
{
    protected static ?string $model = Outstanding::class;

    protected static ?string $modelLabel = 'Outstanding';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\TextInput::make('number')
                                ->label('No. Tiket')
                                ->default('SP-' .Carbon::now()->format('ym').''.(random_int(100000, 999999)))
                                ->disabled()
                                ->dehydrated()
                                ->required()
                                ->columnSpanFull()
                                ->maxLength(32)
                                ->unique(Outstanding::class, 'number', ignoreRecord: true),
                            Forms\Components\Select::make('location_id')
                                ->label('Lokasi')
                                ->options(Location::query()->pluck('name', 'id'))
                                ->live()
                                ->columnSpan(2)
                                ->searchable()
                                ->required(),
                            Forms\Components\Select::make('product_id')
                                ->label('Produk')
                                ->columnSpan(2)
                                ->options(fn (Get $get): Collection => Contract::query()
                                    ->where('location_id', $get('location_id'))
                                    ->join('products', 'products.id', '=', 'contracts.product_id')
                                    ->pluck('products.name', 'products.id'))
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
                                ->columnSpanFull(),
                        ])
                        ->columns(4),

                    // Forms\Components\Fieldset::make('Informasi tanggal')
                    //     ->schema([
                    //         Forms\Components\DatePicker::make('date_in')
                    //             ->label('Awal')
                    //             ->default(now())
                    //             ->maxDate(now())
                    //             ->native(false)
                    //             ->required(),
                    //         Forms\Components\DatePicker::make('date_visit')
                    //             ->label('Visit/Remote')
                    //             ->native(false),
                    //             // ->maxDate(now()),
                    //         Forms\Components\DatePicker::make('date_finish')
                    //             ->label('Selesai')
                    //             ->native(false)
                    //             ->maxDate(now()),
                    //     ])
                    //     ->columns(3),

                    Forms\Components\Fieldset::make('Foto')
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('image')
                                ->hiddenLabel()
                                ->image()
                                ->imageEditor()
                                ->resize(20)
                                ->openable()
                                ->collection('outstandings')
                                ->columnSpanFull(),
                                ]),
                ])
                ->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Fieldset::make('Informasi status')
                        // ->columns([
                        //     'sm' => 3,
                        //     'lg' => 3,
                        // ])
                        ->schema([
                            Forms\Components\Checkbox::make('lpm')
                                ->label('LPM'),
                                // ->columnSpan(['sm' => 1]),
                            Forms\Components\Checkbox::make('status')
                                ->label('Closed'),
                                // ->columnSpan(['sm' => 1]),
                            Forms\Components\Checkbox::make('is_implement')
                                ->label('Imple'),
                                // ->columnSpan(['sm' => 1]),
                            Forms\Components\Checkbox::make('is_oncall')
                                ->label('Oncall'),
                            Grid::make(3)
                                ->schema([
                                    Forms\Components\DatePicker::make('date_in')
                                        ->label('Lapor')
                                        // ->columnSpan(3)
                                        ->default(now())
                                        ->maxDate(now())
                                        ->native(false)
                                        ->required(),
                                    Forms\Components\DatePicker::make('date_visit')
                                        ->label('Visit/Remote')
                                        // ->columnSpanFull()
                                        ->native(false),
                                        // ->maxDate(now()),
                                    Forms\Components\DatePicker::make('date_finish')
                                        ->label('Selesai')
                                        // ->columnSpanFull()
                                        ->native(false)
                                        ->maxDate(now()),
                                ]),
                            Forms\Components\Select::make('priority')
                                ->label('Priority')
                                ->options(OutstandingPriority::class)
                                ->default('normal')
                                ->columnSpanFull(),
                            // ->columnSpan(['sm' => 1]),
                            Forms\Components\ToggleButtons::make('is_type_problem')
                                ->label('Tipe Problem')
                                ->options([
                                    '3' => 'H/W-Non',
                                    '1' => 'H/W',
                                    '2' => 'S/W',
                                    '4' => 'Sipil'
                                ])
                                ->columnSpanFull()
                                ->default(3)
                                ->required()
                                ->inline(),
                                // ->inlineLabel(false),
                            TableRepeater::make('outstandingunits')
                                ->label('Unit')
                                ->collapsible()
                                ->relationship()
                                ->headers([
                                    Header::make('nama')->width('200px'),
                                    Header::make('qty')->width('50px'),
                                ])
                                // ->renderHeader(false)
                                ->streamlined()
                                ->columnSpanFull()
                                ->defaultItems(0)
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
                                ]),
                        ]),
                    // Forms\Components\Section::make('Gambar')
                    //     ->schema([
                    //         SpatieMediaLibraryFileUpload::make('image')
                    //             ->imageEditor()
                    //             ->resize(20)
                    //             ->openable(),
                    //     ])
                    //     ->collapsed(),

                    // Forms\Components\Section::make('Unit')
                    //     ->schema([
                    //         TableRepeater::make('outstandingunits')
                    //             ->label('')
                    //             ->collapsible()
                    //             ->relationship()
                    //             ->headers([
                    //                 Header::make('nama')->width('200px'),
                    //                 Header::make('qty')->width('50px'),
                    //             ])
                    //             // ->renderHeader(false)
                    //             ->streamlined()
                    //             ->schema([
                    //                 Forms\Components\Select::make('unit_id')
                    //                     ->label('Unit')
                    //                     ->options(Unit::where('is_visible', 1)->pluck('name', 'id'))
                    //                     ->placeholder('Pilih unit')
                    //                     ->searchable()
                    //                     ->required(),
                    //                     // ->columnSpan([
                    //                     //     'md' => 7,
                    //                     // ]),

                    //                 Forms\Components\TextInput::make('qty')
                    //                     ->numeric()
                    //                     ->minValue(1)
                    //                     ->maxValue(20)
                    //                     ->default(1)
                    //                     ->required(),
                    //                     // ->columnSpan([
                    //                     //     'md' => 3,
                    //                     // ]),
                    //             ])
                    //         ->defaultItems(0),
                    //         // ->columns([
                    //         //     'md' => 10,
                    //         // ]),
                    //     ])
                    //     ->collapsible(),
                ])
                ->columnSpan(['lg' => 1]),

            // Forms\Components\Group::make()
            //     ->schema([
            //         static::getItemsRepeater(),
            //     ])
            //     ->columnSpanFull(),
        ])
        ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('number')
                ->label('No. Tiket')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
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
            Tables\Columns\TextColumn::make('reportings_count')
                ->label('Aksi')
                ->suffix('x')
                ->sortable()
                ->counts('reportings'),
            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->sortable(),
            Tables\Columns\TextColumn::make('outstandingunits.unit.name')
                ->label('Unit')
                ->badge(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('sla')
                    ->query(function (Builder $query, array $data) {
                        switch ($data['value']) {
                            case 'sla1':
                                return $query->whereRaw('DATEDIFF(date_finish, date_visit) BETWEEN 0 AND 3');
                            case 'sla2':
                                return $query->whereRaw('DATEDIFF(date_finish, date_visit) BETWEEN 4 AND 7');
                            case 'sla3':
                                return $query->whereRaw('DATEDIFF(date_finish, date_visit) > 7');
                        }
                    })
                    ->form([
                        Forms\Components\Select::make('value')
                            ->options([
                                'sla1' => 'SLA 1 (0 - 3)',
                                'sla2' => 'SLA 2 (4 - 7)',
                                'sla3' => 'SLA 3 (> 7)',
                            ])
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->hiddenLabel()->tooltip('Ubah'),
                Tables\Actions\DeleteAction::make()->hiddenLabel()->tooltip('Hapus'),
                Tables\Actions\ForceDeleteAction::make()->hiddenLabel()->tooltip('Hapus selamanya'),
                Tables\Actions\RestoreAction::make()->hiddenLabel()->tooltip('Kembalikan data'),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            // Pages\ViewPost::class,
            Pages\EditOutstanding::class,
            Pages\ManageOutstandingReport::class,
        ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\ReportingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOutstandings::route('/'),
            'create' => Pages\CreateOutstanding::route('/create'),
            'edit' => Pages\EditOutstanding::route('/{record}/edit'),
            'reportings' => Pages\ManageOutstandingReport::route('/{record}/reportings'),
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
