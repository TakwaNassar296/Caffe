<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchDriver extends Model
{
    /** @use HasFactory<\Database\Factories\BranchDriverFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'driver_id',
    ];
}
