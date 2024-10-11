<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ItemMaintenance extends Model implements HasMedia
{
    use HasFactory;
    use HasUlids;
    use InteractsWithMedia;

    protected $fillable = [
        'maintenance_id',
        'title',
        'sort',
        'unit',
        'before',
        'after'
    ];

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class);
    }
}
