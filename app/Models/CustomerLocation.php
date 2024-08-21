<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CustomerLocation extends Pivot
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'customer_locations';

    protected $fillable = [
        'customer_id',
        'location_id',
        'is_to'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
