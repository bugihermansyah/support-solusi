<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuickOutstandingResource\Pages;
use App\Filament\Resources\QuickOutstandingResource\RelationManagers;
use App\Models\Location;
use App\Models\Outstanding;
use App\Models\QuickOutstanding;
use App\Models\Product;
use App\Models\Team;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuickOutstandingResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = QuickOutstanding::class;

    protected static ?string $modelLabel = 'Quick Outstanding';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-start-on-rectangle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('create_user_id')
                    ->label('-')
                    ->default(auth()->user()->id)
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Forms\Components\TextInput::make('number')
                    ->label('No. Tiket')
                    ->default('SP-' .Carbon::now()->format('ym').''.(random_int(100000, 999999)))
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->columnSpan(2)
                    ->maxLength(32)
                    ->unique(Outstanding::class, 'number', ignoreRecord: true),
                Forms\Components\Select::make('location_id')
                    ->label('Lokasi')
                    ->options(Location::query()->pluck('name', 'id'))
                    ->live()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('user_id', Location::find($state)?->user_id ?? ''))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('product_id')
                    ->label('Produk')
                    ->options(function (callable $get){
                        $product = Location::find($get('location_id'));

                        if (!$product) {
                            return Product::all()->pluck('name', 'id');
                        }
                        if ($product->contracts) {
                            return $product->contracts->pluck('product.name', 'product.id');
                        }
                        return collect();
                    })
                    ->required(),
                Forms\Components\Select::make('reporter')
                    ->label('Pelapor')
                    ->options([
                        'client' => 'Client',
                        'preventif' => 'Preventif',
                        'support' => 'Support',
                    ])
                    ->default('client')
                    ->required(),
                Forms\Components\DatePicker::make('date_in')
                    ->label('Lapor')
                    ->default(Carbon::now())
                    ->native(false)
                    ->required(),
                Forms\Components\DatePicker::make('date_visit')
                    ->label('Jadwal')
                    ->default(Carbon::now())
                    ->native(false)
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
                Forms\Components\TextInput::make('title')
                    ->label('Laporan masalah')
                    ->maxLength(100)
                    ->required()
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageQuickOutstandings::route('/'),
        ];
    }
}
