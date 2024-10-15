<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestRenew extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'contract_id',
        'location_id',
        'renewal_date',
        'renewal_periode',
        'description',
        'type',
        'status'
    ];

    public function renewUnits(): HasMany
    {
        return $this->hasMany(RenewUnit::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
