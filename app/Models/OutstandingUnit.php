<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutstandingUnit extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'outstanding_id',
        'unit_id',
        'qty'
    ];
}
