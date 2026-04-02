<?php

namespace App\Filament\Resources\BranchMaterialShipmentResource\Pages;

use App\Filament\Resources\BranchMaterialShipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBranchMaterialShipments extends ListRecords
{
    protected static string $resource = BranchMaterialShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
