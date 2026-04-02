<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    /** @use HasFactory<\Database\Factories\CartItemFactory> */
    use HasFactory;

    protected $fillable = ['cart_id', 'product_id', 'quantity', "total_price" ,"original_price","discount_price"];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function optionValues()
    {
        return $this->belongsToMany(ProductValue::class, 'cart_item_option_values');
           
    }
}
