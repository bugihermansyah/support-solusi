<?php

namespace App\Filament\Support\Resources;

use App\Filament\Support\Resources\BorrowResource\Pages;
use App\Filament\Support\Resources\BorrowResource\RelationManagers;
use App\Models\Loan;
use App\Models\Location;
use App\Models\Unit;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class BorrowResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static ?string $modelLabel = 'Permintaan Unit';

    protected static ?string $navigationGroup = 'Gudang';

    protected static ?int $navigationSort = 1;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $userTeam = $user ? $user->getTeamId() : null ;

        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label('No. Tiket')
                            ->default('WS-' .Carbon::now()->format('ym').''.(random_int(100000, 999999)))
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(32)
                            ->unique(Loan::class, 'number', ignoreRecord: true),
                        Forms\Components\Select::make('location_id')
                            ->label('Lokasi')
                            ->searchable()
                            ->options(Location::where('team_id', $userTeam)->get()->pluck('name_alias', 'id'))
                            ->required(),
                        Forms\Components\TextInput::make('remark'),
                        Forms\Components\RichEditor::make('note')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                                'underLine',

                            ])
                    ])->columnSpan([
                        'lg' => 1
                    ]),

                Section::make()
                    ->schema([
                        TableRepeater::make('LoanUnits')
                            ->label('Peminjaman Unit')
                            ->relationship('loanUnits')
                            ->addActionLabel('Tambah unit')
                            ->deletable(fn (?Model $record) => $record && $record->created_at)
                            ->addable(fn (?Model $record) => $record && $record->created_at)
                            ->reorderable(false)
                            ->minItems(1)
                            ->schema([
                                Forms\Components\Select::make('unit_id')
                                    ->label('Unit')
                                    ->options(Unit::where('is_warehouse', 1)->pluck('name', 'id'))
                                    ->searchable()
                                    ->disabled(fn (?Model $record) => $record && $record->created_at)
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                Forms\Components\TextInput::make('qty')
                                    ->numeric()
                                    ->integer()
                                    ->disabled(fn (?Model $record) => $record && $record->created_at)
                                    ->default(1)
                                    ->minValue(1),
                            ])
                            ->colStyles([
                                'unit_id' => 'width: 1300px;',
                                'qty' => 'width: 70px;',
                            ])
                            ->reorderable()
                            ->collapsible()
                            ->columnSpan('full'),
                    ])->columnSpan([
                        'lg' => 2
                    ])
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->defaultSort('created_at', 'desc')
        ->columns([
            TextColumn::make('number')
                ->label('No. Request')
                ->searchable(),
            TextColumn::make('location.name')
                ->label('Lokasi')
                ->searchable(),
            TextColumn::make('loanunits_count')
                ->label('Unit')
                ->counts('loanunits'),
            TextColumn::make('loanunits_sum_qty')
                ->label('Pinjam')
                ->sum('loanunits', 'qty'),
            TextColumn::make('returnunits_sum_qty')
                ->label('Kembali')
                ->sum([
                    'returnunits' => fn (Builder $query) => $query->whereNotNull('accepted_at'),
                ], 'qty'),
            TextColumn::make('user.name')
                ->label('Peminjam')
                ->searchable(),
            TextColumn::make('remark')
                ->label('Remark'),
            TextColumn::make('note')
                ->label('Ket.')
                ->html(),
            TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime(),
        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\EditBorrow::class,
            Pages\ManageReturn::class,
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
            'index' => Pages\ListBorrows::route('/'),
            'create' => Pages\CreateBorrow::route('/create'),
            'edit' => Pages\EditBorrow::route('/{record}/edit'),
            'return' => Pages\ManageReturn::route('/{record}/return'),
        ];
    }
}
