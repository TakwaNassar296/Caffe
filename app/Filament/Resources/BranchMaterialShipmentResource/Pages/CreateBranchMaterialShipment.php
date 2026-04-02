<?php

namespace App\Filament\Resources\BranchMaterialShipmentResource\Pages;

use App\Filament\Resources\BranchMaterialShipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBranchMaterialShipment extends CreateRecord
{
    protected static string $resource = BranchMaterialShipmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set status to 'sent' for shipments
        $data['status'] = 'sent';
        
        // Set sent_date and shipment_date to transaction_date if not set
        if (!isset($data['sent_date']) && isset($data['transaction_date'])) {
            $data['sent_date'] = $data['transaction_date'];
        }
        if (!isset($data['shipment_date']) && isset($data['transaction_date'])) {
            $data['shipment_date'] = $data['transaction_date'];
        }
        
        // Set default values
        $data['consumer_type'] = $data['consumer_type'] ?? 'branch';
        if (!isset($data['consumer_name']) && isset($data['branch_id'])) {
            $branch = \App\Models\Branch::find($data['branch_id']);
            $data['consumer_name'] = $branch->name ?? 'Branch';
        } else {
            $data['consumer_name'] = $data['consumer_name'] ?? 'Branch';
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Update branch_material quantity_in_stock by adding shipment quantity
        $branchMaterial = $this->record->branchMaterial;
        if ($branchMaterial) {
            $branchMaterial->quantity_in_stock = ($branchMaterial->quantity_in_stock ?? 0) + $this->record->quantity;
            $branchMaterial->save();
        }
    }
}
