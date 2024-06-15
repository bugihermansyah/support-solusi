<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationCustomer extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'customer_locations';

    protected $fillable = [
        'customer_id',
        'location_id'
    ];
}
