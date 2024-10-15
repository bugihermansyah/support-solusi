<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequestRenewResource\Pages;
use App\Filament\Resources\RequestRenewResource\RelationManagers;
use App\Models\Contract;
use App\Models\Location;
use App\Models\RequestRenew;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class RequestRenewResource extends Resource
{
    protected static ?string $model = RequestRenew::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Maintenance';

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $userTeam = $user ? $user->getTeamId() : null ;

        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make()
                        ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('location_id')
                                    ->label('Location')
                                    ->searchable()
                                    ->options(Location::where('team_id', $userTeam)->get()->pluck('name_alias', 'id'))
                                    ->live()
                                    ->required(),
                                Forms\Components\Select::make('contract_id')
                                    ->label('Contract')
                                    ->options(fn (Get $get): Collection => Contract::query()
                                        ->join('products', 'products.id', '=', 'product_id')
                                        ->where('location_id', $get('location_id'))
                                        ->pluck('products.name', 'contracts.id'))
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $contract = Contract::where('contracts.id', $state)->first();
                                
                                        $set('renewal_periode', $contract ? $contract->periode : 0);
                                    }),
                                Forms\Components\Select::make('type')
                                    ->label('Type renewal')
                                    ->options([
                                        'part' => 'Part',
                                        'contract' => 'Contract',
                                    ])
                                    ->default('part')
                                    ->native(false)
                                    ->required(),
                                Forms\Components\TextInput::make('renewal_periode')
                                    ->label('Periode')
                                    ->numeric()
                                    ->minValue(1)
                                    ->suffix('Bulan')
                                    ->required(),
                                Forms\Components\DatePicker::make('renewal_date')
                                    ->label('Date')
                                    ->required(),
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'success' => 'Success',
                                        'cancel' => 'Cancel',
                                    ])
                                    ->default('pending')
                                    ->native(false)
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->columnSpanFull(),
                                ])
                            ])
                    ])
                    ->columnSpan(['lg' => 1]),
                Group::make()
                    ->schema([
                        TableRepeater::make('renewUnits')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('unit_id')
                                    ->label('Unit')
                                    ->options(Unit::where('is_visible', 1)->pluck('name', 'id'))
                                    ->searchable()
                                    ->placeholder('Pilih unit')
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                Forms\Components\TextInput::make('qty')
                                    ->numeric()
                                    ->integer()
                                    ->default(1)
                                    ->required()
                                    ->maxValue(20)
                                    ->minValue(1),
                            ])
                            ->colStyles([
                                'unit_id' => 'width: 1300px;',
                                'qty' => 'width: 70px;',
                            ])
                            ->defaultItems(1)
                            ->distinct()
                            ->collapsible()
                            ->columnSpan('full'),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('location.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contract.product.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn ($state) => ucwords($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('renewal_date'),
                Tables\Columns\TextColumn::make('renewal_periode'),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn ($state) => ucwords($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequestRenews::route('/'),
            'create' => Pages\CreateRequestRenew::route('/create'),
            'edit' => Pages\EditRequestRenew::route('/{record}/edit'),
        ];
    }
}
