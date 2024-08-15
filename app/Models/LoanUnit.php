<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanUnit extends Model
{
    use HasFactory;
    use HasUlids;

    protected $table = 'warehouse_loan_units';

    protected $fillable = [
        'loan_id',
        'unit_id',
        'qty',
        'return_qty'
    ];

    public function returns()
    {
        return $this->hasMany(ReturnUnit::class, 'loan_id', 'loan_id')
                    ->where('unit_id', $this->unit_id)
                    ->whereNotnull('accepted_at')
                    ->where('rejected_at', null);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
