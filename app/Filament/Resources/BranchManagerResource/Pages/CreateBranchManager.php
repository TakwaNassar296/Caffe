<?php

namespace App\Filament\Resources\BranchManagerResource\Pages;

use App\Filament\Resources\BranchManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBranchManager extends CreateRecord
{
    protected static string $resource = BranchManagerResource::class;

    protected function afterCreate(): void
    {
        $this->record->assignRole('branch_manger');
    }
}