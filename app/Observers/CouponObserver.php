<?php

namespace App\Observers;

use App\Models\Coupon;
use Carbon\Carbon;
class CouponObserver
{
    /**
     * Handle the Coupon "created" event.
     */
    public function created(Coupon $coupon): void
    {
        //
    }

    /**
     * Handle the Coupon "updated" event.
     */
    public function updated(Coupon $coupon): void
    {
        //
    }

    /**
     * Handle the Coupon "deleted" event.
     */
    public function deleted(Coupon $coupon): void
    {
        //
    }

    /**
     * Handle the Coupon "restored" event.
     */
    public function restored(Coupon $coupon): void
    {
        //
    }

    /**
     * Handle the Coupon "force deleted" event.
     */
    public function forceDeleted(Coupon $coupon): void
    {
        //
    }

    public function saving(Coupon $coupon): void
    {
        $now = Carbon::now();

        $isExpired = $coupon->end_date && Carbon::parse($coupon->end_date)->isPast();

        $isUsageFull = $coupon->usage_limit !== null && $coupon->used >= $coupon->usage_limit;

        if ($isExpired || $isUsageFull) {
            $coupon->is_active = 0;
        }
    }
}
