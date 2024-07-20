<?php

namespace App\Models;

use App\Enums\OutstandingStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Outstanding extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'number',
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
        'status',
        'user_id',
        'create_user_id'
    ];

    protected $casts = [
        'status' => OutstandingStatus::class,
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
