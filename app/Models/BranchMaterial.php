<?php

namespace App\Models;

use App\Observers\BranchMaterialObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy((BranchMaterialObserver::class))]
class BranchMaterial extends Model
{
    protected $fillable=[
        'branch_id','material_id','quantity_in_stock','unit','current_quantity','max_limit','min_limit',
    ];
         
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function orderBranches()
    {
        return $this->hasMany(OrderBranch::class, 'branch_material_id');
    }

    public function shipments()
    {
        return $this->hasMany(BranchMaterialHistory::class)->where('status', 'sent');
    }

    public function consumptions()
    {
        return $this->hasMany(BranchMaterialHistory::class)->where('status', 'consumed');
    }

    public function history()
    {
        return $this->hasMany(BranchMaterialHistory::class);
    }

    /**
     * Calculate remaining quantity
     * remaining_quantity = quantity_in_stock - current_quantity
     */
    public function getRemainingQuantityAttribute(): float
    {
        $quantityInStock = (float) ($this->quantity_in_stock ?? 0);
        $currentQuantity = (float) ($this->current_quantity ?? 0);
        return max(0, $quantityInStock - $currentQuantity);
    }
}
 