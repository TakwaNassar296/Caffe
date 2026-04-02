<?php

namespace App\Models;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    use HasFactory;
    protected $table='coupon_usages';
    protected $fillable = [
        'user_id',
        'coupon_id',
        'used_at',
        'contract_uuid'
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


public function calculateDiscountedPrice($contract)
{
    $discount = 0;

    if ($this->coupon) {
        if($this->coupon->date_end < now()){

            return $contract->getPriceContractAttribute() ;

        }

        if ($this->coupon->type_coupon === 'ratio') {
            $discount = ($this->coupon->value_coupon / 100) * $contract->getPriceContractAttribute();
        } elseif ($this->coupon->type_coupon === 'value') {
            $discount = $this->coupon->value_coupon;
        }
    }
       
   
    return $contract->getPriceContractAttribute() - $discount;
}


}
