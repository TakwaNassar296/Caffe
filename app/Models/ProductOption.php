<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    /** @use HasFactory<\Database\Factories\ProductOptionFactory> */
    use HasFactory;

    protected $fillable = ['name', 'product_id'];

    public function values()
    {
        return $this->hasMany(ProductValue::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
