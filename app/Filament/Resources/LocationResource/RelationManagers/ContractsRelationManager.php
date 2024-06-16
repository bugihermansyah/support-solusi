<?php

namespace App\Filament\Resources\LocationResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'contracts';

    protected static ?string $recordTitleAttribute = 'product';

    protected static ?string $title = 'Daftar Produk';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Produk')
                    ->options(Product::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('gate'),
                Forms\Components\TextInput::make('type_contract'),
                Forms\Components\DatePicker::make('bap'),
                Forms\Components\TextInput::make('description'),
                Forms\Components\TextInput::make('status'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk'),
                Tables\Columns\TextColumn::make('gate')
                    ->label('Unit'),
                Tables\Columns\TextColumn::make('type_contract')
                    ->label('Kontrak'),
                Tables\Columns\TextColumn::make('bap')
                    ->label('BAP')
                    ->date(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status'),
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
