<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLocation extends Model
{
    /** @use HasFactory<\Database\Factories\UserLocationFactory> */
    use HasFactory;

     protected $fillable = [
        'user_id',
        'name_address',
        'building_number',
        'floor',
        'apartment',
        'address_description',
        'address_title',
        'latitude',
        'longitude',
        'first_name',
        'last_name',
        'phone_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
