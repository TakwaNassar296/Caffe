<?php

namespace App\Models;

use App\Observers\MaterialObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy((MaterialObserver::class))]
class Material extends Model
{
    protected $fillable=[
        'name','quantity_in_stock','unit','current_quantity_material', 'code','category_id','image','status','material_type'
    ];

         
    public function requestMaterials()
    {
       return $this->belongsTo(RequestMaterial::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function branchMaterial()
    {
        return $this->hasMany(BranchMaterial::class);
    }

     public function convertStockTo(string $targetUnit): float
    {
        $stock = $this->quantity_in_stock;

        $conversionRates = [
            'kg' => ['g' => 1000],
            'g'  => ['kg' => 0.001],
            'l'  => ['ml' => 1000],
            'ml' => ['l'  => 0.001],
        ];

        if (isset($conversionRates[$this->unit][$targetUnit])) {
            return $stock * $conversionRates[$this->unit][$targetUnit];
        }

        return $stock; // same unit or unsupported conversion
    }

    /**
     * Validate quantity against available stock.
     */
    public function validateQuantity(float $value, string $selectedUnit): ?string
    {
        $availableStock = $this->convertStockTo($selectedUnit);

        if ($value > $availableStock) {
            return __('الكمية المدخلة أكبر من المخزون المتاح (:max :unit)', [
                'max'  => $availableStock,
                'unit' => $selectedUnit
            ]);
        }

        return null;  
    }

    protected static function booted()
    {
        static::creating(function ($material) {
            $material->code = rand(10000, 99999);
        });
    }
}
