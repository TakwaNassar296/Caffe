<?php

namespace App\Filament\Resources\BranchManagerResource\Pages;

use App\Filament\Resources\BranchManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBranchManagers extends ListRecords
{
    protected static string $resource = BranchManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
