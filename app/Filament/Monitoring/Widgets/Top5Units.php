<?php

namespace App\Filament\Monitoring\Widgets;

use App\Models\Outstanding;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

class Top5Units extends BaseWidget
{
    protected static ?int $sort = 4;

    public function getTableRecordKey(Model $record): string
    {
        return (string) $record->id; // Return the ID of the model instance
    }

    public function table(Table $table): Table
    {
        $topUnits = Outstanding::query()
            ->join('outstanding_units as ou', 'outstandings.id', '=', 'ou.outstanding_id')
            ->join('units as u', 'u.id', '=', 'ou.unit_id')
            ->select('u.name', DB::raw('COUNT(ou.unit_id) as total_units'))
            ->whereMonth('outstandings.date_in', Carbon::now()->month) // Current month
            ->whereYear('outstandings.date_in', Carbon::now()->year)   // Current year
            ->where('u.name', '!=', 'tidak ada kerusakan')
            ->groupBy('u.id','u.name')
            ->orderByDesc('total_units')
            ->limit(5);
        return $table
            ->paginated(false)
            ->heading('Top 5 Units this month')
            ->poll('30s')
            ->query($topUnits)
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('total_units'),
            ]);
    }
}
