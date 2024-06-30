<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvaluationResource\Pages;
use App\Filament\Resources\EvaluationResource\RelationManagers;
use App\Models\Assessment;
use App\Models\Evaluation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class EvaluationResource extends Resource
{
    protected static ?string $model = Evaluation::class;

    protected static ?string $modelLabel = 'Penilaian';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Indicator Performances';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal')
                    ->default(Carbon::now())
                    ->native(false)
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('Support')
                    ->options(function () {
                        $currentUser = Auth::user();

                        $users = User::where('team_id', $currentUser->team_id)->pluck('firstname', 'id');

                        return $users;
                    })
                    ->required(),
                Forms\Components\Select::make('assessment_id')
                    ->label('Penilaian')
                    ->options(Assessment::all()->pluck('title', 'id'))
                    ->reactive()
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('point', Assessment::find($state)?->point ?? 0))
                    ->searchable()
                    ->required()
                    ->columnSpan([
                        'md' => 2,
                    ]),
                Forms\Components\TextInput::make('point')
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required()
                    ->columnSpan([
                        'md' => 0,
                    ]),
                Forms\Components\Textarea::make('note')
                    ->required()
                    ->columnSpanFull(),
            ])
            ->columns(5);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.firstname')
                    ->label('Support')
                    ->searchable(),
                Tables\Columns\TextColumn::make('assessment.title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('point')
                    ->summarize(Sum::make())
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('note'),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEvaluations::route('/'),
        ];
    }
}
