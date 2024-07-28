<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\Event;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Reporting extends Model implements HasMedia, Eventable
{
    use HasFactory;
    use HasUlids;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'outstanding_id',
        'cause',
        'action',
        'solution',
        'work',
        'date_visit',
        // 'user_id',
        'status',
        'revisit',
        'note',
        'user_created_at'
    ];

    protected $casts = [
        'status' => ReportStatus::class,
    ];

    public function toEvent(): Event|array {
        return Event::make($this)
            ->title($this->cause)
            ->start($this->date_visit)
            ->end($this->date_visit);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'reporting_users', 'reporting_id', 'user_id')->withTimestamps();
    }

    /**
     * Get the user that owns the Reporting
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the outstanding that owns the Reporting
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function outstanding(): BelongsTo
    {
        return $this->belongsTo(Outstanding::class);
    }

    /**
     * Get all of the outstandingunits for the Outstanding
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function outstandingunits(): HasMany
    {
        return $this->hasMany(OutstandingUnit::class, 'outstanding_id', 'outstanding_id');
    }

    public function getTitle(): string
    {
        return $this->status;
    }

    public function getStart(): string
    {
        return $this->date_visit;
    }

    public function getEnd(): string
    {
        return $this->date_visit;
    }
}
