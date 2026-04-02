<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductsMaterial extends Model
{
    protected $fillable = [
         'product_id',
        'product_option_id',
         
       
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

     public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(ProductMaterialItem::class);
    }

    public function productOption()
    {
        return $this->belongsTo(ProductOption::class);
    }

    /**
     * Get the equivalent amount of material needed for the given product option.
     *
     * Example:
     * - Product option name = "1kg"
     * - Quantity used = 200 grams of material
     * - Returns how much material is needed for that option size.
     *
     * @return float|null
     */
    public function getNumberOfMaterialEqualProduct()
    {
        $productOption = $this->productOption;
        if (!$productOption || empty($productOption->name)) {
            return null;
        }

         [$optionSize, $optionUnit] = $this->parseSizeAndUnit($productOption->name);

        if (!$optionSize || !$optionUnit) {
            return null;
        }

         $sizeInSameUnit = $this->convertUnit($optionSize, $optionUnit, $this->unit);

        // Calculate total material needed
        return $sizeInSameUnit * $this->quantity_used;
    }

   
    protected function convertUnit($value, $fromUnit, $toUnit)
    {
        $unitMap = [
            'g'   => ['factor' => 1,    'type' => 'weight'],
            'kg'  => ['factor' => 1000, 'type' => 'weight'],
            'ml'  => ['factor' => 1,    'type' => 'volume'],
            'l'   => ['factor' => 1000, 'type' => 'volume'],
            'pcs' => ['factor' => 1,    'type' => 'count'],
        ];

        if (!isset($unitMap[$fromUnit]) || !isset($unitMap[$toUnit])) {
            return $value; 
        }

         if ($unitMap[$fromUnit]['type'] !== $unitMap[$toUnit]['type']) {
            return $value;
        }

      
        $valueInBase = $value * $unitMap[$fromUnit]['factor'];

        return $valueInBase / $unitMap[$toUnit]['factor'];
    }

 
    protected function parseSizeAndUnit($sizeString)
    {
        if (preg_match('/([\d\.]+)\s*(g|kg|ml|l|pcs)/i', trim($sizeString), $matches)) {
            $value = (float) $matches[1];
            $unit = strtolower($matches[2]);
            return [$value, $unit];
        }
        return [null, null];
    }
}
