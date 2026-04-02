<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItemOptionValue extends Model
{
    /** @use HasFactory<\Database\Factories\CartItemOptionValueFactory> */
    use HasFactory;

    protected $fillable = ['cart_item_id', 'product_value_id'];

    public function cartItem()
    {
        return $this->belongsTo(CartItem::class);
    }

    public function productValue()
    {
        return $this->belongsTo(ProductValue::class, 'product_value_id');
    }
}
