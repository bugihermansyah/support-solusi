<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;
    use HasUlids;

    protected $table = 'warehouse_loans';

    protected $fillable = [
        'number',
        'location_id',
        'loan_at',
        'return_at',
        'remark',
        'note',
        'user_id',
    ];

    public function loanUnits(): HasMany
    {
        return $this->hasMany(LoanUnit::class);
    }

    public function returnUnits(): HasMany
    {
        return $this->hasMany(ReturnUnit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
