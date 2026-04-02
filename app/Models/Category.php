<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    protected $fillable=['is_active','name',"image"];


    
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }
}
