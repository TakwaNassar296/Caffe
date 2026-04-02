<?php

namespace App\Filament\Resources\BranchMaterialResource\Pages;

use App\Filament\Resources\BranchMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBranchMaterial extends EditRecord
{
    protected static string $resource = BranchMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
