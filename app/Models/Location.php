<?php

namespace App\Models;

use App\Enums\LocationStatus;
use App\Enums\TypeContract;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'team_id',
        'bd_id',
        'area_status',
        'user_id',
        'image',
        'address',
        'description',
        'type_contract',
        'status',
        'is_default'
    ];

    protected $casts = [
        'status' => LocationStatus::class,
        'type_contract' => TypeContract::class,
        'is_default' => 'boolean',
    ];

    public function getNameAliasAttribute()
    {
        if ($this->company && $this->company->alias) {
            return "{$this->name} - {$this->company->alias}";
        }

        return "{$this->name}";
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_locations')
            ->withPivot('is_to');
    }

    public function customerlocations(): HasMany
    {
        return $this->hasMany(CustomerLocation::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function outstandings(): HasMany
    {
        return $this->hasMany(Outstanding::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bd(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bd_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
