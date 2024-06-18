<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reporting extends Model
{
    use HasFactory;
    use HasUlids;

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
