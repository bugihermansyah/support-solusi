<?php

namespace App\Filament\Support\Resources;

use App\Enums\OutstandingStatus;
use App\Filament\Support\Resources\OutstandingResource\Pages;
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
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class OutstandingResource extends Resource
{
    protected static ?string $model = Outstanding::class;

    // protected static ?string $pluralLabel = 'Outstanding';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 1;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

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
                                ->required()
                                ->disabled(),
                            Forms\Components\Select::make('product_id')
                                ->label('Produk')
                                ->disabled()
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
                                ->disabled()
                                ->required(),
                            Forms\Components\TextInput::make('title')
                                ->label('Laporan masalah')
                                ->disabled()
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->columns(4),
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
                        // ->collapsed(),
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
        ->defaultSort('created_at', 'desc')
        ->columns([
            Tables\Columns\TextColumn::make('number')
                ->label('No. Tiket')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('location.team.name')
                ->label('Tim Area')
                ->searchable()
                ->limit(5)
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('location.company.alias')
                ->label('Group')
                ->searchable(),
            Tables\Columns\TextColumn::make('location.name')
                ->label('Lokasi')
                ->searchable()
                ->sortable()
                ->limit(15),
            Tables\Columns\TextColumn::make('product.name')
                ->label('Produk')
                ->searchable()
                ->limit(13),
            Tables\Columns\TextColumn::make('title')
                ->label('Masalah')
                // ->limit(20)
                ->searchable(),
            Tables\Columns\TextColumn::make('reporter')
                ->label('Pelapor')
                ->formatStateUsing(fn ($state) => ucwords($state))
                ->colors([
                    'danger' => 'client',
                    'warning' => 'preventif',
                    'success' => 'support',
                ])
                ->searchable(),
            Tables\Columns\TextColumn::make('date_in')
                ->label('Lapor')
                ->date()
                ->sortable(),
            Tables\Columns\TextColumn::make('date_visit')
                ->label('Pertama')
                ->date(),
            Tables\Columns\TextColumn::make('date_finish')
                ->label('Selesai')
                ->date(),
            Tables\Columns\TextColumn::make('reportings_count')
                ->label('Aksi')
                ->suffix('x')
                ->sortable()
                ->counts('reportings'),
            Tables\Columns\TextColumn::make('is_type_problem')
                ->label('Problem')
                ->badge()
                ->searchable(),
            Tables\Columns\TextColumn::make('outstandingunits.unit.name')
                ->label('Unit')
                ->badge()
                ->searchable(),
            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->searchable(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->hiddenLabel()->tooltip('Ubah'),
                // Tables\Actions\DeleteAction::make()->hiddenLabel()->tooltip('Hapus'),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('number')
            ]);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            // Pages\ViewOutstanding::class,
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
            // 'create' => Pages\CreateOutstanding::route('/create'),
            'edit' => Pages\EditOutstanding::route('/{record}/edit'),
            // 'view' => Pages\ViewOutstanding::route('/{record}'),
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
