<?php

namespace App\Observers;

use App\Models\Branch;
use App\Models\Material;

class BranchObserver
{
    /**
     * Handle the Branch "created" event.
     */
    public function created(Branch $branch): void
    {
       
    }

    /**
     * Handle the Branch "updated" event.
     */
   public function updated(Branch $branch): void
{
 
    if (!empty($branch->branchMaterial) && is_iterable($branch->branchMaterial)) {
        foreach ($branch->branchMaterial as $item) {
             $oldItem = $item->getOriginal();
 
            $oldQty = $oldItem['quantity_in_stock'] ?? 0;
            $newQty = $item['quantity_in_stock'] ?? 0;

             $material = Material::find($item['material_id']);
            if ($material) {
                $diff = $newQty - $oldQty;

                 
                $material->update([
                    'quantity_in_stock' => $material->quantity_in_stock - $diff,
                ]);
            }
        }
    }
}

    /**
     * Handle the Branch "deleted" event.
     */
    public function deleted(Branch $branch): void
    {
        //
    }

    /**
     * Handle the Branch "restored" event.
     */
    public function restored(Branch $branch): void
    {
        //
    }

    /**
     * Handle the Branch "force deleted" event.
     */
    public function forceDeleted(Branch $branch): void
    {
        //
    }
}
