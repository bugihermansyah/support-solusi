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
        return $this->belongsToMany(Customer::class, 'customer_locations')
            ->withPivot('is_to');
    }

    /**
     * Get all of the locationcustomers for the Location
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function customerlocations(): HasMany
    {
        return $this->hasMany(CustomerLocation::class);
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

    /**
     * Get the BD that owns the Location
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bd(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bd_id');
    }

    /**
     * Get the company that owns the Location
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
