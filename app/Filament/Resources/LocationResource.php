<?php

namespace App\Filament\Resources;

use App\Enums\LocationStatus;
use App\Enums\TypeContract;
use App\Filament\Resources\LocationResource\Api\Transformers\LocationTransformer;
use App\Filament\Resources\LocationResource\Pages;
use App\Filament\Resources\LocationResource\RelationManagers;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Team;
use App\Models\User;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $pluralLabel = 'Lokasi';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Main';

    public static function getApiTransformer()
    {
        return LocationTransformer::class;
    }

    public static function form(Form $form): Form
    {
        $bdUsers = User::role('BD')->pluck('firstname', 'id');

        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Select::make('company_id')
                                    ->label('Perusahaan')
                                    ->relationship('company', 'alias')
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('alias')
                                            ->required()
                                            ->maxLength(50),
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('tlp')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email address')
                                            ->required()
                                            ->email()
                                            ->maxLength(255)
                                            ->unique(),
                                    ])
                                    ->createOptionAction(function (Action $action) {
                                        return $action
                                            ->modalHeading('Buat Perusahaan')
                                            ->modalSubmitActionLabel('Buat Perusahaan')
                                            ->modalWidth('lg');
                                    }),
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('team_id')
                                    ->label('Tim')
                                    ->options(Team::all()->pluck('name', 'id')),
                                Forms\Components\Select::make('bd_id')
                                    ->label('Marketing')
                                    ->options($bdUsers)
                                    ->searchable(),
                                Forms\Components\Radio::make('area_status')
                                    ->label('Area Lokasi?')
                                    ->options([
                                        'in' => 'Dalam Kota',
                                        'out' => 'Luar Kota',
                                    ])
                                    ->default('in')
                                    ->inline()
                                    ->inlineLabel(false)
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
                                    ->searchable(),
                                Forms\Components\ToggleButtons::make('type_contract')
                                    ->label('Tipe Kontrak')
                                    ->inline()
                                    ->options(TypeContract::class)
                                    ->default('sewa')
                                    ->required(),
                                Forms\Components\ToggleButtons::make('status')
                                    ->inline()
                                    ->options(LocationStatus::class)
                                    ->required(),
                                Forms\Components\Textarea::make('address')
                                    ->label('Alamat'),
                                Forms\Components\Textarea::make('description')
                                    ->label('Deskripsi'),
                                // Forms\Components\TextInput::make('latitude')
                                //     ->hiddenLabel(),
                                // Forms\Components\TextInput::make('longitude')
                                //     ->hiddenLabel(),
                                // Map::make('location')
                                //     ->label('Location')
                                //     ->columnSpanFull()
                                //     ->defaultLocation(latitude: -6.1361598453121, longitude: 106.8556022166)
                                //     ->draggable(true)
                                //     ->clickable(true) // click to move marker
                                //     ->zoom(15)
                                //     ->minZoom(0)
                                //     ->maxZoom(28)
                                //     ->tilesUrl("https://tile.openstreetmap.de/{z}/{x}/{y}.png")
                                //     ->detectRetina(true)
                                //     ->extraStyles([
                                //         'min-height: 40vh'
                                //     ])
                                //     ->extraControl(['customControl' => true])
                                //     ->extraTileControl(['customTileOption' => 'value'])
                                //     ->afterStateUpdated(function (Set $set, ?array $state): void {
                                //         $lat = $state['lat'] ?? null;
                                //         $lng = $state['lng'] ?? null;
                                //         $geojson = isset($state['geojson']) ? json_encode($state['geojson']) : null;

                                //         $set('latitude', $lat);
                                //         $set('longitude', $lng);
                                //         $set('geojson', $geojson);
                                //     })
                                //     ->afterStateHydrated(function ($state, $record, Set $set): void {
                                //         if ($record) {
                                //             $set('location', [
                                //                 'lat' => $record->latitude,
                                //                 'lng' => $record->longitude,
                                //                 'geojson' => $record->geojson ? json_decode($record->geojson) : null,
                                //             ]);
                                //         }
                                //     })
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Tabs::make('Tabs')
                            ->tabs([
                                Tabs\Tab::make('Mail Notifications')
                                    ->icon('heroicon-m-bell')
                                    ->schema([
                                        TableRepeater::make('customerlocations')
                                            ->label('Pelanggan')
                                            ->relationship()
                                            // ->itemLabel(fn (array $state): ?string => $state['email'] ?? null)
                                            ->deleteAction(
                                                fn(Action $action) => $action->requiresConfirmation(),
                                            )
                                            ->schema([
                                                Forms\Components\Select::make('customer_id')
                                                    ->label('Email')
                                                    ->options(Customer::all()->pluck('name_email', 'id'))
                                                    ->searchable()
                                                    ->distinct()
                                                    ->required(),
                                                Forms\Components\Toggle::make('is_to')
                                                    ->label('CC/To')
                                                    ->inline(false)
                                                    ->required(),
                                            ])
                                            ->colStyles([
                                                'customer_id' => 'width: 900px;',
                                                'is_to' => 'width: 100px;',
                                            ])
                                            // ->schema([
                                            //     Forms\Components\Grid::make(4)
                                            //         ->schema([
                                            //             Forms\Components\Select::make('customer_id')
                                            //                 ->label('Nama')
                                            //                 ->relationship('customer', 'email')
                                            //                 ->preload()
                                            //                 // ->live(onBlur:true)
                                            //                 ->columnSpan(3)
                                            //                 ->distinct()
                                            //                 ->searchable()
                                            //                 ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                            //                 ->required()
                                            //                 ->createOptionForm([
                                            //                     Forms\Components\TextInput::make('name')
                                            //                         ->required()
                                            //                         ->maxLength(255),
                                            //                     Forms\Components\TextInput::make('tlp')
                                            //                         ->maxLength(255),
                                            //                     Forms\Components\TextInput::make('email')
                                            //                         ->label('Email address')
                                            //                         ->required()
                                            //                         ->email()
                                            //                         ->maxLength(255)
                                            //                         ->unique(),
                                            //                 ])
                                            //                 ->createOptionAction(function (Action $action) {
                                            //                     return $action
                                            //                         ->modalHeading('Buat Pelanggan')
                                            //                         ->modalSubmitActionLabel('Buat Pelanggan')
                                            //                         ->modalWidth('lg');
                                            //                 }),
                                            //             Forms\Components\Toggle::make('is_to')
                                            //                 ->label('CC/To')
                                            //                 ->inline(false)
                                            //         ])
                                            // ])
                                            ->collapsible()
                                            ->defaultItems(0),
                                    ]),
                                // ...
                            ])
                    ])
                    // ->schema([
                    //     Forms\Components\Section::make()
                    //         ->schema([
                    //             Forms\Components\DatePicker::make('first_project')
                    //                 ->label('Tanggal Proyek')
                    //                 ->native(false),
                    //         ]),

                    //     Forms\Components\Section::make()
                    //         ->schema([
                    //             Forms\Components\Repeater::make('customerlocations')
                    //                 ->label('Pelanggan')
                    //                 ->relationship()
                    //                 ->schema([
                    //                     Forms\Components\Select::make('customer_id')
                    //                         ->label('Nama')
                    //                         ->relationship('customer', 'name')
                    //                         ->preload()
                    //                         ->searchable()
                    //                         ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    //                         ->required()
                    //                         ->createOptionForm([
                    //                             Forms\Components\TextInput::make('name')
                    //                                 ->required()
                    //                                 ->maxLength(255),
                    //                             Forms\Components\TextInput::make('tlp')
                    //                                 ->maxLength(255),
                    //                             Forms\Components\TextInput::make('email')
                    //                                 ->label('Email address')
                    //                                 ->required()
                    //                                 ->email()
                    //                                 ->maxLength(255)
                    //                                 ->unique(),
                    //                         ])
                    //                         ->createOptionAction(function (Action $action) {
                    //                             return $action
                    //                                 ->modalHeading('Buat Pelanggan')
                    //                                 ->modalSubmitActionLabel('Buat Pelanggan')
                    //                                 ->modalWidth('lg');
                    //                         }),
                    //                 ])
                    //                 ->collapsible()
                    //                 ->defaultItems(0),
                    //         ]),
                    // ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company.alias')
                    ->label('Alias')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Lokasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contracts.product.name')
                    ->label('Produk')
                    ->searchable()
                    ->listWithLineBreaks()
                    ->badge(),
                Tables\Columns\TextColumn::make('contracts.bap')
                    ->label('BAP')
                    ->searchable()
                    ->date()
                    ->listWithLineBreaks()
                    ->badge(),
                Tables\Columns\TextColumn::make('contracts.type_contract')
                    ->label('Kontrak')
                    ->searchable()
                    ->listWithLineBreaks()
                    ->badge(),
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Tim')
                    ->searchable(),
                Tables\Columns\TextColumn::make('area_status')
                    ->label('Kota')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bd.firstname')
                    ->label('BD')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.firstname')
                    ->label('Support')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type_contract')
                    ->label('Kontrak'),
                Tables\Columns\TextColumn::make('status')
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
                Tables\Filters\TrashedFilter::make(),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\ContractsRelationManager::class,
            RelationManagers\OutstadingsRelationManager::class,
            RelationManagers\UnitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
