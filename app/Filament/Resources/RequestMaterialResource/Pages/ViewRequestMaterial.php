<?php

namespace App\Filament\Resources\RequestMaterialResource\Pages;

use App\Filament\Resources\RequestMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRequestMaterial extends ViewRecord
{
    protected static string $resource = RequestMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Material requests are read-only - approvals handled via table actions
        ];
    }
}
