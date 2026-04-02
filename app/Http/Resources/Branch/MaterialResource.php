<?php

namespace App\Http\Resources\Branch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
   public function toArray(Request $request): array
{
    $currentQuantity = $this->current_quantity ?? 0;
    $minLimit = $this->min_limit ?? 0;
    $maxLimit = $this->max_limit ?? 0;

    $status = $this->calculateStockStatus($currentQuantity, $minLimit, $maxLimit);

    return [
        'id'                => $this->material?->id,
        'name'              => $this->material?->name,
        'code'              => $this->material?->code,
        'unit'              => $this->material?->unit,
        'quantity_in_stock' => $this->quantity_in_stock,
        'current_quantity'  => $currentQuantity,
        'stock'             => $status,
        'category_name'=>$this->material->category->name?? null,
        'category_id'=>$this->material->category_id ?? null,
        'status'            => $this->getStatusLabel($status),
        'image'             => $this->material && $this->material->image
                               ? asset('storage/' . $this->material->image)
                               : asset('images/default.png'),
        'max'               => $maxLimit,
        'min'               => $minLimit,
    ];
}

    /**
     * Calculate stock status based on current quantity and limits
     *
     * @param float $currentQuantity
     * @param float $minLimit
     * @param float $maxLimit
     * @return string
     */
    private function calculateStockStatus(float $currentQuantity, float $minLimit, float $maxLimit): string
    {
        // Out of stock: current quantity is 0 or less
        if ($currentQuantity <= 0) {
            return 'out_of_stock';
        }

        // If min_limit is set and current quantity is below it, it's low stock
        if ($minLimit > 0 && $currentQuantity < $minLimit) {
            return 'low_stock';
        }

        // If max_limit is set and current quantity exceeds it, still consider it good
        // (it's above minimum, so it's good, just above maximum)
        if ($minLimit > 0 && $currentQuantity >= $minLimit) {
            return 'good';
        }

        // If no limits are set, use a default threshold
        // Consider it low if quantity is less than 10% of stock, or good otherwise
        $quantityInStock = $this->quantity_in_stock ?? 0;
        if ($quantityInStock > 0) {
            $percentage = ($currentQuantity / $quantityInStock) * 100;
            if ($percentage < 10) {
                return 'low_stock';
            }
        }

        // Default to good if we can't determine otherwise
        return 'good';
    }

    /**
     * Get human-readable status label
     *
     * @param string $status
     * @return string
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'out_of_stock' => __('admin.out_of_stock'),
            'low_stock'    => __('admin.low_stock'),
            'good'         => __('admin.good'),
            default        => __('admin.good'),
        };
    }
}
 