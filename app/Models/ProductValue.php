<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductValue extends Model
{
    /** @use HasFactory<\Database\Factories\ProductValueFactory> */
    use HasFactory;

    protected $fillable = ['product_option_id', 'value', 'extra_price', 'is_recommended'];

    public function productOption()
    {
            return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    public function orderItems()
    {
        return $this->belongsToMany(OrderItem::class, 'order_item_option_values', 'option_value_id', 'order_item_id');
    }
}
