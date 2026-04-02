<?php

namespace App\Filament\Resources\BranchResource\RelationManagers;

use App\Filament\Resources\BranchMaterialResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BranchMaterialRelationManager extends RelationManager
{
    protected static string $relationship = 'branchMaterial';

     public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('strings.branches_materail'); 
    }


    public function form(Form $form): Form
    {
       return BranchMaterialResource::form($form);
    }

    public function table(Table $table): Table
    {
        return BranchMaterialResource::table($table);
    }
}
