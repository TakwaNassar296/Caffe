<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverOrder extends Model
{
    protected $fillable = [
        'driver_id',
        'order_id',
        'branch_id',
    ];
}
