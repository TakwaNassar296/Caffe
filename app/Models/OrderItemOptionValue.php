<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemOptionValue extends Model
{
    /** @use HasFactory<\Database\Factories\OrderItemOptionValueFactory> */
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'option_value_id',
    ];

     public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function optionValue()
    {
        return $this->belongsTo(ProductValue::class, 'option_value_id');
    }
}
