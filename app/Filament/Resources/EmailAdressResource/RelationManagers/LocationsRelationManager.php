<?php

namespace App\Filament\Resources\EmailAdressResource\RelationManagers;

use App\Models\Company;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

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
            ->recordTitleAttribute('name_alias')
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
                Action::make('attachLocations')
                    ->label('Attach Locations')
                    ->form($this->getAttachLocationsFormSchema())
                    ->action(function (array $data) {
                        $this->attachLocations($data);
                    })
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    protected function getAttachLocationsFormSchema(): array
    {
        return [
            Select::make('company')
                ->options(Company::query()->pluck('alias', 'id'))
                ->searchable()
                ->live(),
            Forms\Components\Select::make('locations')
                ->label('Locations')
                ->multiple()
                ->distinct()
                ->options(fn (Get $get): Collection => Location::query()
                    ->where('company_id', $get('company'))
                    ->pluck('name', 'id'))
                ->required(),
            Forms\Components\Toggle::make('is_to')
                ->label('CC / To')
                ->default(false),
        ];
    }

    public function attachLocations(array $data)
    {
        foreach ($data['locations'] as $locationId) {
            $this->getOwnerRecord()->locations()->attach($locationId, ['is_to' => $data['is_to']]);
        }
    }
}
