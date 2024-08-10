<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'tlp',
        'email',
        'description'
    ];

    public function getNameEmailAttribute()
    {
        return "{$this->name} <{$this->email}>";
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'customer_locations')
            ->withPivot('is_to');
    }
}
