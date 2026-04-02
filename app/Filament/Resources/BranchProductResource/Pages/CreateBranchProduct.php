<?php

namespace App\Filament\Resources\BranchProductResource\Pages;

use App\Filament\Resources\BranchProductResource;
use App\Models\BranchMaterial;
use App\Models\ProductsMaterial;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateBranchProduct extends CreateRecord
{
    protected static string $resource = BranchProductResource::class;

    protected function afterCreate(): void
    {
        $this->updateBranchMaterials($this->record);
    }

    protected function updateBranchMaterials($branchProduct): void
    {
        if (!$branchProduct->branch_id || !$branchProduct->product_id) {
            return;
        }

        $productAmount = (float) ($branchProduct->amount ?? 0);
        if ($productAmount <= 0) {
            return;
        }

        // Get all ProductsMaterial for this product (all options)
        $productMaterials = ProductsMaterial::where('product_id', $branchProduct->product_id)
            ->with('items.material')
            ->get();

        if ($productMaterials->isEmpty()) {
            return;
        }

        // Group materials by material_id and unit, sum quantities
        $combinedMaterials = [];
        
        foreach ($productMaterials as $pm) {
            foreach ($pm->items as $item) {
                $key = $item->material_id . '_' . $item->unit;
                
                if (!isset($combinedMaterials[$key])) {
                    $combinedMaterials[$key] = [
                        'material_id' => $item->material_id,
                        'unit' => $item->unit,
                        'total_quantity' => 0,
                    ];
                }
                
                // Sum quantity_used (combine if same material and unit)
                $combinedMaterials[$key]['total_quantity'] += (float) $item->quantity_used;
            }
        }

        // Update BranchMaterial for each combined material
        DB::beginTransaction();
        try {
            foreach ($combinedMaterials as $combined) {
                // Calculate total needed: sum of quantity_used × product amount
                $totalQuantity = $combined['total_quantity'] * $productAmount;
                
                // Find or create BranchMaterial
                $branchMaterial = BranchMaterial::firstOrNew([
                    'branch_id' => $branchProduct->branch_id,
                    'material_id' => $combined['material_id'],
                ]);

                // If material already exists, add to existing quantity
                if ($branchMaterial->exists) {
                    $branchMaterial->quantity_in_stock += $totalQuantity;
                    $branchMaterial->current_quantity += $totalQuantity;
                } else {
                    // New material - set initial quantities
                    $branchMaterial->quantity_in_stock = $totalQuantity;
                    $branchMaterial->current_quantity = $totalQuantity;
                }
                
                $branchMaterial->unit = $combined['unit'];
                
                // Save will trigger BranchMaterialObserver which handles central warehouse deduction
                $branchMaterial->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
