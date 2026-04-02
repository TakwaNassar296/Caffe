<?php

namespace App\Models;

use App\Observers\CouponObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy((CouponObserver::class))]
class Coupon extends Model
{
    use HasFactory;

    protected $fillable=[
        'name','code','type','value','start_date','end_date','usage_limit','used','is_active','kind','branch_id'
    ];

    public function product()
    {
        return $this->hasMany(Product::class);
    }
       public function orders()
    {
        return $this->hasMany(Order::class);
    }

      public function branchs()
    {
        // Branch table uses `coupons_id` as the foreign key
        return $this->hasMany(Branch::class, 'coupons_id');
    }

        /**
         * Calculate the discount amount for a given subtotal.
         *
         * @param  float  $subtotal
         * @return float
         */
        public function calculateDiscount(float $subtotal): float
        {
            $discount = $this->type === 'fixed'
                ? $this->value
                : ($this->value / 100) * $subtotal;

            return min($discount, $subtotal);
        }

        /**
         * Check if coupon is valid for current date and usage.
         *
         * @return bool
         */
        public function isValid(): bool
        {
            return $this->is_active
                && now()->between($this->start_date, $this->end_date)
                && (is_null($this->usage_limit) || $this->used < $this->usage_limit);
        }

}
