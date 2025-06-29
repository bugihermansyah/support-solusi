<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Reporting extends Model implements HasMedia
{
    use HasFactory;
    use HasUlids;
    use InteractsWithMedia;
    // use SoftDeletes;

    protected $fillable = [
        'outstanding_id',
        'cause',
        'action',
        'solution',
        'work',
        'date_visit',
        'status',
        'revisit',
        'note',
        'signature',
        'start_work',
        'end_work',
        'send_mail_at',
        'user_created_at',
        'email_to',
        'email_cc'
    ];

    protected $casts = [
        'status' => ReportStatus::class,
        'email_to' => 'array',
        'email_cc' => 'array'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'reporting_users', 'reporting_id', 'user_id')
            ->withTimestamps();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function outstanding(): BelongsTo
    {
        return $this->belongsTo(Outstanding::class);
    }

    public function outstandingunits(): HasMany
    {
        return $this->hasMany(OutstandingUnit::class, 'outstanding_id', 'outstanding_id');
    }

    public function reportingUsers(): HasMany
    {
        return $this->hasMany(ReportingUser::class);
    }
}
