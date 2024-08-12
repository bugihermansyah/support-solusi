<?php

namespace App\Filament\Clusters\Units\Resources;

use App\Filament\Clusters\Units;
use App\Filament\Clusters\Units\Resources\UnitResource\Pages;
use App\Filament\Clusters\Units\Resources\UnitResource\RelationManagers;
use App\Models\Unit;
use App\Models\UnitCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $cluster = Units::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?int $navigationSort = 0;
    // protected static ?string $navigationGroup = 'Main';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Foto unit')
                            ->image()
                            ->imageEditor()
                            ->resize(20)
                            ->openable()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\Select::make('unit_category_id')
                            ->label('Kategori')
                            ->options(UnitCategory::all()->pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('parent_id')
                            ->label('Set Unit')
                            ->options(Unit::where('parent_id', null)->pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $set('is_visible', true);
                                } else {
                                    $set('is_visible', false);
                                }
                            }),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Terlihat')
                            ->required(),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Warehouse')
                    ->visible(auth()->user()->hasRole('admin'))
                    ->schema([
                        Forms\Components\TextInput::make('qty')
                            ->label('Stock')
                            ->numeric()
                            ->required(),
                        Forms\Components\Toggle::make('is_warehouse')
                            ->label('Warehouse')
                            ->inline(false)
                            ->required(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto')
                    ->circular(),
                    // ->size(50),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Set Unit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unitCategory.name')
                    ->label('Kategori')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Terlihat')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_warehouse')
                    ->label('Gudang')
                    ->boolean(),
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
            ])
            ->defaultSort('sort')
            ->reorderable('sort');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Clusters\Units\Resources\UnitResource\Pages\ManageUnits::route('/'),
        ];
    }
}
