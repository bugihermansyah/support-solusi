<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuickOutstanding extends Model
{
    use HasFactory;
    use HasUlids;

    protected $table = 'outstandings';

    protected $fillable = [
        'number',
        'location_id',
        'product_id',
        'title',
        'reporter',
        'date_in',
        'date_visit',
        'user_id',
        'create_user_id'
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

    /**
     * Get the product that owns the Outstanding
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
