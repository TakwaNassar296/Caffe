<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BranchMaterialHistory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_active',
        'name',
        'points',
        'stars',
        'title',
        'description',
        'price',
        'coupon_id',
        'category_id',
        'rating',
        'total_rating',
        'amount',
        'delivery_time',
        'image',
        'options',
        'total_sales',
        'perpar_steps',
        'appere_in_cart',
        'is_offer',
        'is_offer_percentage',
        'discount_rate',
        'price_after_discount',
        "price_with_points",
        'stat_minutes',
        'end_minutes',
    ];

    protected function casts(): array
    {
        return [
            'image' => 'array',
            'active'=>'boolean',
            'perpar_steps'=>'array',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }


   

    /**
     * Accessor to calculate the price after applying a discount coupon.
     *
     * @return float
     */
    /**
     * Get remaining quantity (amount - total_sales).
     * Calculated: on create = amount, after sales = amount - total_sales.
     */
    public function getRemainingQuantityAttribute(): int
    {
        $amount = (int) ($this->attributes['amount'] ?? 0);
        $totalSales = (int) ($this->attributes['total_sales'] ?? 0);

        return max(0, $amount - $totalSales);
    }

    public function calcPrice(): float
    {
        $coupon = $this->coupon;

        if (!$coupon || !$coupon->value) {
            return $this->price;
        }

        if ($coupon->type === 'percent') {
            $discountAmount = $this->price * $coupon->value / 100;
        } elseif ($coupon->type === 'fixed') {
            $discountAmount = $coupon->value;
        } else {
            $discountAmount = 0;
        }

        return max(round($this->price - $discountAmount, 2), 0);
    }

   



    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_product');
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class, 'product_id');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function reviewsCount(): int
    {
        return $this->reviews()->count();
    }

    public function averageRating(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

  public function relatedProducts()
{
    return $this->belongsToMany(
        Product::class,
        'related_products',
        'product_id',
        'related_product_id'
    )->select('products.id', 'products.name');  
}

   


    public function scopeFilter($query, $filters)
{
    return $query
         ->when(!empty($filters['name']), fn($q) =>
            $q->where('name', 'like', '%' . $filters['name'] . '%')
        )

         ->when(!empty($filters['category_id']), fn($q) =>
            $q->where('category_id', $filters['category_id'])
        )

         ->when(!empty($filters['is_top_rated']), fn($q) =>
            $q->orderByDesc('reviews_avg_rating')
        )

         ->when(!empty($filters['highest_price']), fn($q) =>
            $q->orderByDesc('price')
        )

         ->when(!empty($filters['lowest_price']), fn($q) =>
            $q->orderBy('price', 'asc')
        )

         ->when(!empty($filters['min_price']), fn($q) =>
            $q->where('price', '>=', $filters['min_price'])
        )

         ->when(!empty($filters['max_price']), fn($q) =>
            $q->where('price', '<=', $filters['max_price'])
        )

         ->when(!empty($filters['heights_points']), fn($q) =>
            $q->orderByDesc('points')
        )

         ->when(!empty($filters['heights_stars']), fn($q) =>
            $q->orderByDesc('stars')
        )

         ->when(!empty($filters['fast_delivery']), fn($q) =>
            $q->orderByRaw('(end_minutes - stat_minutes) ASC')
        );
}

    public function favourites()
    {
        return $this->hasMany(Favourite::class);
    }

public function getDeliveryProductTimeAttribute()
{
    $start = (float) $this->stat_minutes; 
    $end = (float) $this->end_minutes;
    
    return $end . ' - ' . $start;  
}

/**
 * Get products materials relationship
 */
public function productsMaterials()
{
    return $this->hasMany(ProductsMaterial::class);
}

/**
 * Get material consumption history for this product
 * Custom relationship using query builder
 */
public function materialConsumptions()
{
    // Get all material IDs used in this product
    $materialIds = $this->productsMaterials()
        ->with('items.material')
        ->get()
        ->flatMap(function ($productMaterial) {
            return $productMaterial->items->pluck('material.id')->filter();
        })
        ->unique()
        ->toArray();

    if (empty($materialIds)) {
        // Return empty Eloquent query builder, not raw query
        return BranchMaterialHistory::query()->whereRaw('1 = 0');
    }

    // Get consumption history for these materials where order contains this product
    return BranchMaterialHistory::query()
        ->whereIn('material_id', $materialIds)
        ->where('status', 'consumed')
        ->whereHas('order.items', function ($query) {
            $query->where('product_id', $this->id);
        });
}

    
}
