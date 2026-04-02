<?php

namespace App\Filament\Resources\BranchMaterialResource\Pages;

use App\Filament\Resources\BranchMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBranchMaterial extends ViewRecord
{
    protected static string $resource = BranchMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
