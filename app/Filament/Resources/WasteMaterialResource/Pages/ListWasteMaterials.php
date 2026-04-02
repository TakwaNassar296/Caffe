<?php

namespace App\Filament\Resources\WasteMaterialResource\Pages;

use App\Filament\Resources\WasteMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWasteMaterials extends ListRecords
{
    protected static string $resource = WasteMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->hidden(fn () => auth('admin')->user()->role === 'super_admin'),
        ];
    }
}
