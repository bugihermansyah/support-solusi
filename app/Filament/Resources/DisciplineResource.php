<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DisciplineResource\Pages;
use App\Filament\Resources\DisciplineResource\RelationManagers;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DisciplineResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Disiplin';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Indicator Performances';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                RepeatableEntry::make('evaluations')
                    ->schema([
                        TextEntry::make('date'),
                        TextEntry::make('assessment.title'),
                        TextEntry::make('point'),
                        TextEntry::make('note'),
                    ])
                    ->columns(4)
                    // ->columnSpanFull(),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('firstname')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('evaluations_sum_point')
                    ->sum('evaluations', 'point')
                    ->label('Minus Score')
                    ->getStateUsing(function ($record) {
                        return $record->evaluations_sum_point ?? 0;
                    }),
                Tables\Columns\TextColumn::make('base_score')
                    ->default(100)
                    ->hidden(),
                Tables\Columns\TextColumn::make('credit_score')
                    ->label('Credit Score')
                    ->weight(FontWeight::Bold)
                    ->size(TextColumnSize::Large)
                    ->getStateUsing(function ($record) {
                        $baseScore = $record->base_score ?? 100;
                        $evaluationsSumPoint = $record->evaluations_sum_point ?? 0;
                        return $baseScore - $evaluationsSumPoint;
                    }),
            ])
            ->filters([
                SelectFilter::make('month')
                    ->label('Month')
                    ->options([
                        '01' => 'January',
                        '02' => 'February',
                        '03' => 'March',
                        '04' => 'April',
                        '05' => 'May',
                        '06' => 'June',
                        '07' => 'July',
                        '08' => 'August',
                        '09' => 'September',
                        '10' => 'October',
                        '11' => 'November',
                        '12' => 'December',
                    ])
                    ->default(Carbon::now()->format('m'))
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereRelation('evaluations', function ($query) use ($data) {
                                $query->whereMonth('date', $data['value']);
                            });
                        }
                    }),
                SelectFilter::make('year')
                    ->label('Year')
                    ->options(function () {
                        $years = range(Carbon::now()->year, 2021);
                        return array_combine($years, $years);
                    })
                    ->default(Carbon::now()->year)
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereRelation('evaluations', function ($query) use ($data) {
                                $query->whereYear('date', $data['value']);
                            });
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListDisciplines::route('/'),
            'view' => Pages\ViewDiscipline::route('/{record}'),
            // 'create' => Pages\CreateDiscipline::route('/create'),
            'edit' => Pages\EditDiscipline::route('/{record}/edit'),
        ];
    }
}
