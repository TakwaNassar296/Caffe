<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    /** @use HasFactory<\Database\Factories\VehicleTypeFactory> */
    use HasFactory;

    protected $fillable = ['name', 'icon'];

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    public function getIconAttribute()
    {
        return asset('storage/' . $this->attributes['icon']);
    }

}
