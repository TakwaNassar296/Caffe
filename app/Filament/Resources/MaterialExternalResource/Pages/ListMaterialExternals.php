<?php

namespace App\Filament\Resources\MaterialExternalResource\Pages;

use App\Filament\Resources\MaterialExternalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMaterialExternals extends ListRecords
{
    protected static string $resource = MaterialExternalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
