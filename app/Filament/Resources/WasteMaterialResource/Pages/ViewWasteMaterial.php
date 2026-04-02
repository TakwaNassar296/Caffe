<?php

namespace App\Filament\Resources\WasteMaterialResource\Pages;

use App\Filament\Resources\WasteMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWasteMaterial extends ViewRecord
{
    protected static string $resource = WasteMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
