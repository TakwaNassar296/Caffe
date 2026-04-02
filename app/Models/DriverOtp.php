<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverOtp extends Model
{
    /** @use HasFactory<\Database\Factories\DriverOtpFactory> */
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'otp',
        'last_resend',
    ];
}
