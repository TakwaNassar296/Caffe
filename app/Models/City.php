<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'is_active',
        'code',
        'governorate_id',
    ];

    protected $hidden = [
        'latitude',
        'longitude',
        'created_at',
        'updated_at',
        'is_active'
    ];

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }
}
