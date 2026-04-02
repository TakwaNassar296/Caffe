<?php

namespace App\Filament\Resources\BranchManagerResource\Pages;

use App\Filament\Resources\BranchManagerResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ViewBranchManager extends ViewRecord
{
    protected static string $resource = BranchManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(__("admin.branch_manager_information"))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('strings.name')),
                        
                        TextEntry::make('email')
                            ->label(__('strings.email')),
                        
                        TextEntry::make('assignedBranch.name')
                            ->label(__('admin.branch'))
                            ->badge()
                            ->color('success'),

                        TextEntry::make('role')
                            ->label(__('strings.role'))
                            ->badge()
                            ->color('info'),

                        TextEntry::make('created_at')
                            ->label(__('strings.created_at'))
                            ->dateTime(),
                    
                    ])->columns(2),
            ]);
    }
}