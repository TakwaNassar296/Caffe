<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationOtp extends Model
{
    /** @use HasFactory<\Database\Factories\VerificationOtpFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'otp', 'last_resend'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     public function getGenerateCodeOtpAttribute(): string
    {
        return rand(1111, 9999);
    }
}
