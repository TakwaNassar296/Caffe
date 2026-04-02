<?php

namespace App\Filament\Resources\BranchResource\Pages;

use App\Filament\Resources\BranchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Product;
class EditBranch extends EditRecord
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    
     protected function afterSave(): void
{
    if ($this->data['select_all_products'] ?? false) {
        $this->record->products()->sync(
            Product::pluck('id')->toArray()
        );
    }
}
    
    // public function getRelationManagers(): array
    // {
    //     return [];
    // } 
 
}
