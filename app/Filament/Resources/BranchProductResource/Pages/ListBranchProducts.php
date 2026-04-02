<?php

namespace App\Filament\Resources\BranchProductResource\Pages;

use App\Filament\Resources\BranchProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBranchProducts extends ListRecords
{
    protected static string $resource = BranchProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
