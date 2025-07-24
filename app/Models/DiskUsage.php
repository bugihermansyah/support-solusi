<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiskUsage extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'location_id',
        'mount_point',
        'size',
        'used',
        'available',
        'usage_percent',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
