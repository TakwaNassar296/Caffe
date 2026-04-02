<?php

namespace App\Filament\Resources\MaterialRequestApprovalResource\Pages;

use App\Filament\Resources\MaterialRequestApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMaterialRequestApproval extends ViewRecord
{
    protected static string $resource = MaterialRequestApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Approval history is read-only
        ];
    }
}
