<?php

namespace App\Filament\Resources\BranchProductResource\Pages;

use App\Filament\Resources\BranchProductResource;
use App\Models\BranchMaterial;
use App\Models\ProductsMaterial;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditBranchProduct extends EditRecord
{
    protected static string $resource = BranchProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->updateBranchMaterials($this->record);
    }

    protected function updateBranchMaterials($branchProduct): void
    {
        if (!$branchProduct->branch_id || !$branchProduct->product_id) {
            return;
        }

        $productAmount = (float) ($branchProduct->amount ?? 0);
        $originalProductId = $branchProduct->getOriginal('product_id');
        $originalAmount = (float) ($branchProduct->getOriginal('amount') ?? 0);

        DB::beginTransaction();
        try {
            // If product changed, remove materials from old product first
            if ($originalProductId && $originalProductId != $branchProduct->product_id && $originalAmount > 0) {
                $this->removeProductMaterials($branchProduct->branch_id, $originalProductId, $originalAmount);
            }

            // Add/update materials for current product
            if ($productAmount > 0) {
                $this->addProductMaterials($branchProduct->branch_id, $branchProduct->product_id, $productAmount, $originalProductId == $branchProduct->product_id ? $originalAmount : 0);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function removeProductMaterials($branchId, $productId, $productAmount): void
    {
        $productMaterials = ProductsMaterial::where('product_id', $productId)
            ->with('items.material')
            ->get();

        if ($productMaterials->isEmpty()) {
            return;
        }

        // Group materials by material_id and unit
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
                
                $combinedMaterials[$key]['total_quantity'] += (float) $item->quantity_used;
            }
        }

        // Remove quantities from BranchMaterial
        foreach ($combinedMaterials as $combined) {
            $totalQuantity = $combined['total_quantity'] * $productAmount;
            
            $branchMaterial = BranchMaterial::where('branch_id', $branchId)
                ->where('material_id', $combined['material_id'])
                ->first();

            if ($branchMaterial) {
                $branchMaterial->quantity_in_stock = max(0, $branchMaterial->quantity_in_stock - $totalQuantity);
                $branchMaterial->current_quantity = max(0, $branchMaterial->current_quantity - $totalQuantity);
                
                // If quantity becomes zero or negative, we might want to delete it or keep it
                // For now, we'll keep it but set to 0
                if ($branchMaterial->quantity_in_stock <= 0) {
                    $branchMaterial->quantity_in_stock = 0;
                    $branchMaterial->current_quantity = 0;
                }
                
                $branchMaterial->save();
            }
        }
    }

    protected function addProductMaterials($branchId, $productId, $productAmount, $originalAmount = 0): void
    {
        $productMaterials = ProductsMaterial::where('product_id', $productId)
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

        // Calculate difference if updating existing product
        $amountDifference = $productAmount - $originalAmount;

        // Update BranchMaterial for each combined material
        foreach ($combinedMaterials as $combined) {
            // Find or create BranchMaterial
            $branchMaterial = BranchMaterial::firstOrNew([
                'branch_id' => $branchId,
                'material_id' => $combined['material_id'],
            ]);

            if ($branchMaterial->exists && $originalAmount > 0) {
                // Updating existing - adjust based on difference
                $quantityDifference = $combined['total_quantity'] * $amountDifference;
                $branchMaterial->quantity_in_stock += $quantityDifference;
                $branchMaterial->current_quantity += $quantityDifference;
            } else {
                // New material - set initial quantities
                $totalQuantity = $combined['total_quantity'] * $productAmount;
                $branchMaterial->quantity_in_stock = $totalQuantity;
                $branchMaterial->current_quantity = $totalQuantity;
            }
            
            $branchMaterial->unit = $combined['unit'];
            
            // Save will trigger BranchMaterialObserver which handles central warehouse deduction
            $branchMaterial->save();
        }
    }
}
