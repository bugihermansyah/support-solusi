<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\InteractsWithMedia;

class Maintenance extends Model
{
    use HasFactory;
    use HasUlids;
    use InteractsWithMedia;

    protected $fillable = [
        'location_id',
        'type',
        'date',
        'archive'
    ];

    public function itemMaintenances(): HasMany
    {
        return $this->hasMany(ItemMaintenance::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
