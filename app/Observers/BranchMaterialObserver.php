<?php

namespace App\Observers;

use App\Models\BranchMaterial;
use App\Models\Material;
use App\Models\OrderBranch;

class BranchMaterialObserver
{
    protected array $conversionRates = [
        'kg' => ['g' => 1000],
        'g'  => ['kg' => 0.001],
        'l'  => ['ml' => 1000],
        'ml' => ['l'  => 0.001],
    ];

    /**
     * Convert a quantity from one unit to another
     */
    protected function convertUnit(float $quantity, string $from, string $to): float
    {
        if ($from === $to) {
            return $quantity;
        }

        return ($this->conversionRates[$from][$to] ?? 1) * $quantity;
    }

    /**
     * Handle the BranchMaterial "created" event.
     */
    public function created(BranchMaterial $branchMaterial): void
    {
        $material = Material::find($branchMaterial->material_id);

        if ($material) {
            $convertedQty = $this->convertUnit(
                $branchMaterial->quantity_in_stock,
                $branchMaterial->unit,
                $material->unit
            );

           //Deduct the quantity from the main warehouse
            $material->update([
                'current_quantity_material' => ($material->current_quantity_material ?? $material->quantity_in_stock) - $convertedQty,
            ]);
 
            // Update the current quantity in the branch
            $branchMaterial->updateQuietly([
                'current_quantity' => $branchMaterial->quantity_in_stock,
            ]);

            // Create related OrderBranch record
            OrderBranch::create([
                'branch_material_id' => $branchMaterial->id,
                'branch_id'          => $branchMaterial->branch_id,
                'status'             => 'pending',
                'reason_of_cancel'   => null,
            ]);

        }
    }

    /**
     * Handle the BranchMaterial "updated" event.
     */
    public function updated(BranchMaterial $branchMaterial): void
    {
        $material = Material::find($branchMaterial->material_id);

        if ($material) {
            $oldQtyConverted = $this->convertUnit(
                $branchMaterial->getOriginal('quantity_in_stock'),
                $branchMaterial->getOriginal('unit'),
                $material->unit
            );

            $newQtyConverted = $this->convertUnit(
                $branchMaterial->quantity_in_stock,
                $branchMaterial->unit,
                $material->unit
            );

            $diff = $newQtyConverted - $oldQtyConverted;

            //Deduct or add the difference to the quantity in the main warehouse
            $material->update([
                'current_quantity_material' => ($material->current_quantity_material ?? $material->quantity_in_stock) - $diff,
            ]);

            // Update the current quantity in the branch
            $branchMaterial->updateQuietly([
                'current_quantity' => $branchMaterial->quantity_in_stock,
            ]);
        }
    }

    /**
     * Handle the BranchMaterial "deleted" event.
     */
    public function deleted(BranchMaterial $branchMaterial): void
    {
        $material = Material::find($branchMaterial->material_id);

        if ($material) {
            $convertedQty = $this->convertUnit(
                $branchMaterial->quantity_in_stock,
                $branchMaterial->unit,
                $material->unit
            );

            // Return the quantity to the main warehouse
            $material->update([
                'current_quantity_material' => ($material->current_quantity_material ?? $material->quantity_in_stock) + $convertedQty,
            ]);
        }
    }
}
      