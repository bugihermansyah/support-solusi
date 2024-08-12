<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnUnit extends Model
{
    use HasFactory;
    use HasUlids;

    protected $table = 'warehouse_return_units';

    protected $fillable = [
        'loan_id',
        'unit_id',
        'qty',
        'remark',
        'comment',
        'accepted_at',
        'rejected_at'
    ];

    protected $casts = [
        // 'rejected_at' => 'boolean',
        // 'accepted_at' => 'boolean'
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
