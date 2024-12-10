<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RenewalLocations extends BaseWidget
{
    protected static ?int $sort = 3;
    // protected int | string | array $columnSpan = '3';

    public function getTableRecordKey($record): string
    {
        return (string) $record->contract_id; // Replace with your unique identifier
    }

    public function table(Table $table): Table
    {
        // $user = Auth::user();
        // $userTeam = $user ? $user->getTeamId() : null ;
        // $isHead = $user && $user->hasRole('head');

        $query = Contract::query()
            ->select(
                'contracts.id as contract_id',
                'locations.name as location_name',
                'products.name as product_name',
                'contracts.gate',
                'contracts.type_contract',
                'contracts.bap',
                'contracts.periode',
                \DB::raw('COALESCE(rr.renewal_date, contracts.bap) AS renewal_date'),
                \DB::raw('COALESCE(rr.renewal_periode, contracts.periode) AS renewal_periode'),
                \DB::raw('COALESCE(rr.renewal_date + INTERVAL rr.renewal_periode MONTH, contracts.bap + INTERVAL contracts.periode MONTH) AS next_due_date'),
                'rr.status AS renewal_status',
                \DB::raw('DATEDIFF(COALESCE(rr.renewal_date + INTERVAL rr.renewal_periode MONTH, contracts.bap + INTERVAL contracts.periode MONTH), NOW()) AS days_to_due')
            )
            ->leftJoin(DB::raw('(SELECT rr1.contract_id, rr1.renewal_date, rr1.renewal_periode, rr1.status 
                                 FROM request_renews rr1 
                                 WHERE rr1.deleted_at IS NULL AND rr1.type = "contract"  
                                 ORDER BY rr1.renewal_date DESC) as rr'),
                        'contracts.id', '=', 'rr.contract_id'
            )
            ->join('locations', 'locations.id', '=', 'contracts.location_id')
            ->join('products', 'products.id', '=', 'contracts.product_id')
            ->whereBetween(
                \DB::raw('DATEDIFF(COALESCE(rr.renewal_date + INTERVAL rr.renewal_periode MONTH, contracts.bap + INTERVAL contracts.periode MONTH), NOW())'),
                [0, 90]
            )
            ->where('contracts.type_contract', 'sewa');

        return $table
            ->defaultPaginationPageOption(5)
            ->query($query)
            // ->defaultSort('reportings.date_visit', 'desc')
            // ->poll('30s')
            ->deferLoading()
            ->columns([
                TextColumn::make('location_name')
                    ->label('Location'),
                TextColumn::make('product_name')
                    ->label('Product'),
                TextColumn::make('bap')
                    ->label('BAP')
                    ->date(),
                TextColumn::make('renewal_date')
                    ->label('Last Renewal')
                    ->date(),
                TextColumn::make('renewal_periode')
                    ->label('Last Periode'),
                TextColumn::make('renewal_status')
                    ->label('Status'),
            ]);
    }
}
