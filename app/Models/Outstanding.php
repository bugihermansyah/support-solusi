<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outstanding extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'location_id',
        'product_id',
        'title',
        'reporter',
        'date_in',
        'date_visit',
        'date_finish',
        'lpm',
        'is_implement',
        'is_type_problem',
        'status'
    ];

    /**
     * Get all of the reportings for the Outstanding
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reportings(): HasMany
    {
        return $this->hasMany(Reporting::class);
    }

    /**
     * Get all of the outstandingunits for the Outstanding
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function outstandingunits(): HasMany
    {
        return $this->hasMany(OutstandingUnit::class);
    }

    /**
     * Get the location that owns the Outstanding
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}