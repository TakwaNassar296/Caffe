<?php

namespace App\Filament\Resources\MaterialExternalResource\Pages;

use App\Filament\Resources\MaterialExternalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMaterialExternal extends CreateRecord
{
    protected static string $resource = MaterialExternalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['material_type'] = 'external';
        return $data;
    }
}
