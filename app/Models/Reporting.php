<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Reporting extends Model implements HasMedia
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
        'user_id',
        'status',
        'note'
    ];

    protected $casts = [
        'status' => ReportStatus::class,
    ];

    /**
     * Get the user that owns the Reporting
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}
