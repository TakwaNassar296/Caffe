<?php

namespace App\Filament\Resources\BranchMaterialShipmentResource\Pages;

use App\Filament\Resources\BranchMaterialShipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBranchMaterialShipment extends EditRecord
{
    protected static string $resource = BranchMaterialShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function () {
                    // Deduct shipment quantity from branch_material quantity_in_stock
                    $branchMaterial = $this->record->branchMaterial;
                    if ($branchMaterial) {
                        $branchMaterial->quantity_in_stock = max(0, ($branchMaterial->quantity_in_stock ?? 0) - $this->record->quantity);
                        $branchMaterial->save();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Store old quantity for comparison
        $this->oldQuantity = $this->record->quantity;
        
        // Set sent_date and shipment_date to transaction_date if not set
        if (!isset($data['sent_date']) && isset($data['transaction_date'])) {
            $data['sent_date'] = $data['transaction_date'];
        }
        if (!isset($data['shipment_date']) && isset($data['transaction_date'])) {
            $data['shipment_date'] = $data['transaction_date'];
        }
        
        return $data;
    }

    protected function afterSave(): void
    {
        // Update branch_material quantity_in_stock based on quantity difference
        $branchMaterial = $this->record->branchMaterial;
        if ($branchMaterial && isset($this->oldQuantity)) {
            $quantityDiff = $this->record->quantity - $this->oldQuantity;
            $branchMaterial->quantity_in_stock = max(0, ($branchMaterial->quantity_in_stock ?? 0) + $quantityDiff);
            $branchMaterial->save();
        }
    }
}
