<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    /** @use HasFactory<\Database\Factories\RefreshTokensFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'refresh_token', 'expires_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
