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

class Location extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'company_id',
        'name',
        'team_id',
        'bd',
        'area_status',
        'user_id',
        'image',
        'address',
        'description',
        'type_contract',
        'status'
    ];

    protected $casts = [
        'status' => LocationStatus::class,
        'type_contract' => TypeContract::class,
    ];

    /**
     * Get the team that owns the Location
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * The customers that belong to the Location
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class);
    }

    /**
     * Get all of the locationcustomers for the Location
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function locationcustomers(): HasMany
    {
        return $this->hasMany(LocationCustomer::class, 'location_id');
    }

    /**
     * Get all of the contracts for the Lycation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Get all of the products for the Location
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the user that owns the Location
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
