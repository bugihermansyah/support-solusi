<?php

namespace App\Models;

use App\Enums\OutstandingPriority;
use App\Enums\OutstandingStatus;
use App\Enums\OutstandingTypeProblem;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Outstanding extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;
    use HasUlids;
    // use SoftDeletes;

    protected $fillable = [
        'number',
        'location_id',
        'product_id',
        'team_id',
        'title',
        'reporter',
        'reporter_name',
        'date_in',
        'date_visit',
        'date_finish',
        'date_temporary',
        'lpm',
        'is_implement',
        'is_type_problem',
        'is_oncall',
        'is_temporary',
        'priority',
        'status',
        'user_id',
        'create_user_id',
        'note'
    ];

    protected $casts = [
        'status' => OutstandingStatus::class,
        'is_type_problem' => OutstandingTypeProblem::class,
        'priority' => OutstandingPriority::class,
        'lpm' => 'boolean',
        'is_implement' => 'boolean',
        'is_oncall' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('outstandings')
            ->singleFile();
    }

    public function reportings(): HasMany
    {
        return $this->hasMany(Reporting::class);
    }


    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'outstanding_units', 'outstanding_id', 'unit_id')
            ->withPivot('deleted_at');
    }

    public function outstandingunits(): HasMany
    {
        return $this->hasMany(OutstandingUnit::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
