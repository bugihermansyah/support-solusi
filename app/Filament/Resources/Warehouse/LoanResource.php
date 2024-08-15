<?php

namespace App\Filament\Resources\Warehouse;

use App\Filament\Resources\Warehouse\LoanResource\Pages;
use App\Filament\Resources\Warehouse\LoanResource\RelationManagers;
use App\Models\Loan;
use App\Models\Unit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section as ComponentsSection;
use Filament\Infolists\Infolist;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static ?string $modelLabel = 'Permintaan Unit';

    protected static ?string $recordTitleAttribute = 'number';

    // protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Gudang';

    protected static ?int $navigationSort = 1;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('number')
                            ->label('No. Request')
                            ->content(fn (Loan $record): string => $record->number),
                        Forms\Components\Placeholder::make('user_id')
                            ->label('Peminjam')
                            ->content(fn (Loan $record): string => $record->user->name),
                        Forms\Components\Placeholder::make('location_id')
                            ->label('Lokasi')
                            ->content(fn (Loan $record): string => $record->location->name),
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Di buat')
                            ->content(fn (Loan $record): string => $record->created_at),
                        TextInput::make('remark'),
                        RichEditor::make('note')
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
                        TableRepeater::make('units')
                            ->label('Daftar Unit')
                            ->relationship('loanUnits')
                            ->schema([
                                Forms\Components\Select::make('unit_id')
                                    ->label('Unit')
                                    ->options(Unit::where('is_warehouse', 1)->pluck('name', 'id'))
                                    ->searchable()
                                    // ->disabled(fn(Model $record)=> $record->processed_at && $record->rejected_at && $record->completed_at)
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                Forms\Components\TextInput::make('qty')
                                    ->numeric()
                                    ->integer()
                                    ->default(1)
                                    ->minValue(1),
                            ])
                            ->colStyles([
                                'unit_id' => 'width: 1300px;',
                                'qty' => 'width: 70px;',
                            ])
                            ->reorderable()
                            ->collapsible()
                            // ->minItems(3)
                            // ->maxItems(5)
                            ->columnSpan('full'),
                    ])->columnSpan([
                        'lg' => 2
                    ])
            ])->columns(3);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make('3')
                    ->schema([
                        ComponentsSection::make()
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name'),
                                Infolists\Components\TextEntry::make('location.name'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->dateTime(),
                                Infolists\Components\TextEntry::make('remark'),
                                Infolists\Components\TextEntry::make('note'),
                            ])->columnSpan(1),
                        ComponentsSection::make()
                            ->schema([
                                TableRepeatableEntry::make('loanUnits')
                                ->label('Daftar unit')
                                ->schema([
                                    Infolists\Components\TextEntry::make('unit.name')
                                        ->label('Nama'),
                                    Infolists\Components\TextEntry::make('qty'),
                                    Infolists\Components\TextEntry::make('return')
                                        ->getStateUsing(function ($record){
                                            $totalReturnQty = $record->returns->sum('qty');
                                            return $totalReturnQty;
                                        }),
                                ])
                                ->striped(),
                            ])->columnSpan(2)
                    ]),
            ]);
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
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->rejected_at) {
                            return 'Ditolak';
                        } elseif ($record->completed_at) {
                            return 'Selesai';
                        } elseif ($record->approved_at) {
                            return 'Disetujui';
                        } elseif ($record->processed_at) {
                            return 'Diproses';
                        } else {
                            return 'Pending';
                        }
                    })
                    ->icons([
                        'heroicon-o-x-circle' => fn ($state): bool => $state === 'Ditolak',
                        'heroicon-o-check-circle' => fn ($state): bool => $state === 'Disetujui',
                        'heroicon-o-arrow-path' => fn ($state): bool => $state === 'Diproses',
                        'heroicon-o-check' => fn ($state): bool => $state === 'Selesai',
                    ])
                    ->colors([
                        'danger' => 'Ditolak',
                        'success' => 'Disetujui',
                        'warning' => 'Diproses',
                        'primary' => 'Selesai',
                        'secondary' => 'Pending',
                    ]),
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
            Pages\EditLoan::class,
            Pages\ViewLoan::class,
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
            'index' => Pages\ListLoans::route('/'),
            'create' => Pages\CreateLoan::route('/create'),
            'edit' => Pages\EditLoan::route('/{record}/edit'),
            'view' => Pages\ViewLoan::route('/{record}'),
            'return' => Pages\ManageReturn::route('/{record}/return'),
        ];
    }
}
