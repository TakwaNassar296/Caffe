<?php

namespace App\Filament\Resources\ProductsMaterialResource\Pages;

use App\Filament\Resources\ProductsMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductsMaterial extends ViewRecord
{
    protected static string $resource = ProductsMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
