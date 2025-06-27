<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'parent_id',
        'sort',
        'image',
        'stock',
        'is_warehouse',
        'is_visible',
        'unit_category_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }

    public function unitCategory(): BelongsTo
    {
        return $this->belongsTo(UnitCategory::class, 'unit_category_id');
    }

    public function outstandings()
    {
        return $this->belongsToMany(Outstanding::class, 'outstanding_units', 'unit_id', 'outstanding_id');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'outstanding_units', 'unit_id', 'locations_id');
    }
}
