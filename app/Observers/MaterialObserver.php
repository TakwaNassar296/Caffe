<?php

namespace App\Observers;

use App\Models\Material;

class MaterialObserver
{
    /**
     * Handle the Material "created" event.
     */
    public function created(Material $material): void
    {
        $material->updateQuietly([
            'current_quantity_material' => $material->quantity_in_stock,
        ]);
    }


    /**
     * Handle the Material "updated" event.
     */
    public function updated(Material $material): void
    {
        //
    }

    /**
     * Handle the Material "deleted" event.
     */
    public function deleted(Material $material): void
    {
        //
    }

    /**
     * Handle the Material "restored" event.
     */
    public function restored(Material $material): void
    {
        //
    }

    /**
     * Handle the Material "force deleted" event.
     */
    public function forceDeleted(Material $material): void
    {
        //
    }
}
