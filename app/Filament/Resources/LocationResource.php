<?php

namespace App\Filament\Resources;

use App\Enums\LocationStatus;
use App\Enums\TypeContract;
use App\Filament\Resources\LocationResource\Pages;
use App\Filament\Resources\LocationResource\RelationManagers;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $modelLabel = 'Lokasi';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Main';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Select::make('company_id')
                                    ->label('Perusahaan')
                                    ->options(Company::all()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('team_id')
                                    ->label('Tim')
                                    ->options(Team::all()->pluck('name', 'id')),
                                Forms\Components\TextInput::make('bd')
                                    ->label('Marketing')
                                    ->maxLength(50),
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
                                    ->label('Alamat')
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Deskripsi'),
                            ])
                        ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                    Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\DatePicker::make('first_project')
                                    ->label('Tanggal Proyek')
                                    ->native(false),
                            ]),

                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Repeater::make('locationcustomers')
                                    ->label('Klien')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('customer_id')
                                            ->label('Nama')
                                            ->options(Customer::query()->pluck('name', 'id'))
                                            ->searchable()
                                            ->required(),
                                    ])
                                    ->collapsible()
                                    ->defaultItems(0),
                            ]),
                    ])
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Cluster')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contracts.product.name')
                    ->label('Produk')
                    ->badge(),
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Tim Area')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bd')
                    ->label('BD')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type_contract')
                    ->label('Tipe Kontrak'),
                Tables\Columns\TextColumn::make('user.firstname')
                    ->label('Staff'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                // Tables\Columns\TextColumn::make('customers.name'),
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
            RelationManagers\ContractsRelationManager::class,
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