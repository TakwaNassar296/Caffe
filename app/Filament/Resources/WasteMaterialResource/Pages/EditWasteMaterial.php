<?php

namespace App\Filament\Resources\WasteMaterialResource\Pages;

use App\Filament\Resources\WasteMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWasteMaterial extends EditRecord
{
    protected static string $resource = WasteMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
