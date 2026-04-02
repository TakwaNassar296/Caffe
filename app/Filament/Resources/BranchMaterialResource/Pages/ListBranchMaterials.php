<?php

namespace App\Filament\Resources\BranchMaterialResource\Pages;

use App\Filament\Resources\BranchMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListBranchMaterials extends ListRecords
{
    protected static string $resource = BranchMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    

}
