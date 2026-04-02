<?php

namespace App\Filament\Resources\MaterialExternalResource\Pages;

use App\Filament\Resources\MaterialExternalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMaterialExternal extends ViewRecord
{
    protected static string $resource = MaterialExternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
