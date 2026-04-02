<?php

namespace App\Filament\Resources\MaterialExternalResource\Pages;

use App\Filament\Resources\MaterialExternalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaterialExternal extends EditRecord
{
    protected static string $resource = MaterialExternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
