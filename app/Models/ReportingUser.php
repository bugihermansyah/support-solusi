<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportingUser extends Model
{
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'reporting_id',
        'user_id',
    ];

    public function reporting(): BelongsTo
    {
        return $this->belongsTo(Reporting::class);
    }
}
