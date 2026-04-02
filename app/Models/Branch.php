<?php

namespace App\Models;

use App\Traits\GeoTrait;
use App\Observers\BranchObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy((BranchObserver::class))]
class Branch extends Model
{
    /** @use HasFactory<\Database\Factories\BranchFactory> */
    use HasFactory;


    protected  $fillable  = [
        'name',
        'description',
        'code',
        'image',
        'opening_date',
        'close_date',
        'scope_work',
        'images',
        'phone_number',
        'latitude',
        'longitude',
        'admin_id',
        'coupons_id',
        'order_receipt_time',
        'city_id',
        'governorate_id',
    ];

    protected $casts = [
        'opening_date' => 'datetime:H:i',
        'close_date'   => 'datetime:H:i',
        'images' => 'array',

    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($branch) {
            \App\Models\BranchSetting::create([
                'branch_id' => $branch->id,
            ]);
        });
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'branch_product')->withPivot('status', 'amount');
    }

    public function activeProducts()
    {
        return $this->belongsToMany(Product::class, 'branch_product')
            ->withPivot('status')
            ->wherePivot('status', 1);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'branch_id');
    }

    public function branchMaterial()
    {
        return $this->hasMany(BranchMaterial::class);
    }

    /**
     * Get all shipments for this branch (status = 'sent')
     */
    public function shipments()
    {
        return $this->hasMany(BranchMaterialHistory::class)->where('status', 'sent');
    }

    /**
     * Get all material consumptions for this branch (status = 'consumed')
     */
    public function materialConsumptions()
    {
        return $this->hasMany(BranchMaterialHistory::class)->where('status', 'consumed');
    }

    /**
     * Get all material history for this branch (both sent and consumed)
     */
    public function materialHistory()
    {
        return $this->hasMany(BranchMaterialHistory::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }


    public static function scopeFilter($query, $filters)
    {
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        return $query;
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
    

    public function coupons()
    {
         return $this->belongsTo(Coupon::class, 'coupons_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function drivers()
    {
        return $this->belongsToMany(Driver::class, 'branch_driver');
    }

    public function wasteMaterials()
    {
        return $this->hasMany(WasteMaterial::class);
    }

    public function favourites()
    {
        return $this->hasMany(Favourite::class);
    }

    public function settings()
    {
        return $this->hasOne(BranchSetting::class);
    }

    public function requestMaterials()
    {
        return $this->hasMany(RequestMaterial::class);
    }

    public function isInsideScope($userLat, $userLng): array
    {
        $distance = GeoTrait::distanceKm(
            $userLat,
            $userLng,
            $this->latitude,
            $this->longitude
        );

        return [
            'inside'   => $distance <= $this->scope_work,
            'distance' => round($distance, 2),
            'scope'    => $this->scope_work,
        ];
    }
}
