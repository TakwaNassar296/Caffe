<?php

namespace App\Filament\Resources\ProductsMaterialResource\Pages;

use App\Filament\Resources\ProductsMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductsMaterial extends EditRecord
{
    protected static string $resource = ProductsMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
