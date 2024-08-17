<?php

namespace App\Filament\Resources\EmailAdressResource\RelationManagers;

use App\Models\Company;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LocationsRelationManager extends RelationManager
{
    protected static string $relationship = 'locations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('company.alias')
                    ->label('Group'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Location'),
                Tables\Columns\ToggleColumn::make('is_to')
                    ->label('CC / To'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->multiple()
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        // Forms\Components\Select::make('alias')
                        //     ->options(Company::all()->pluck('alias', 'id'))
                        //     ->searchable()
                        //     ->live(),
                        $action->getRecordSelect(),
                        // Forms\Components\Select::make('location_id')
                        //     ->options(fn (Get $get): Collection => Location::query()
                        //         ->where('company_id', $get('alias'))
                        //         ->pluck('name', 'id')),
                        Forms\Components\Toggle::make('is_to')
                            ->required(),
                    ]),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DetachBulkAction::make(),
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
