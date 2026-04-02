<?php

namespace App\Models;

use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy((OrderObserver::class))]

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        "user_id",
        "invoice_id",
        "total_price",
        "status",
        "cancelled_reason",
        "coupon_id",
        "discount",
        'branch_id',
        'user_location_id',
        'user_payment_id',
        'tax',
        'delivery_charge',
        'type',
        'pay_with',
        'sub_total',
        'schedule_time',
        'finance',
        'booked_by_driver',
        'qr_token',
        'driver_id',
        'driver_finance',
        'points_increase_user',
        'schedual_date',
        'payment_status',
        'order_num',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function userLocation()
    {
        return $this->belongsTo(UserLocation::class);
    }

    public function UserPayment()
    {
        return $this->belongsTo(UserPayment::class);
    }

    // public function drivers()
    // {
    //     return $this->belongsToMany(Driver::class, 'driver_orders')
    //         ->withPivot('branch_id');
    // }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function calculateFinance()
    {
        $finance = SiteSetting::first()->delivery_charge ?? 20;

        return $finance;
    }

    /**
     * Query Scopes for better code readability and maintainability
     */

    /**
     * Scope a query to only include scheduled orders
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope a query to include orders that are due (past their scheduled time)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\Carbon|null $now
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDue($query, $now = null)
    {
        $now = $now ?? \Carbon\Carbon::now();
        return $query->whereNotNull('schedual_date')
            ->where('schedual_date', '<=', $now);
    }

    /**
     * Scope a query to only include pending orders
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed orders
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
