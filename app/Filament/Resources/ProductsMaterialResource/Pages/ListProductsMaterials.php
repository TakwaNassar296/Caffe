<?php

namespace App\Filament\Resources\ProductsMaterialResource\Pages;

use App\Filament\Resources\ProductsMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductsMaterials extends ListRecords
{
    protected static string $resource = ProductsMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
