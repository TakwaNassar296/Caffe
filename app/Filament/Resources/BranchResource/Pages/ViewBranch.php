<?php

namespace App\Filament\Resources\BranchResource\Pages;

use App\Filament\Resources\BranchResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewBranch extends ViewRecord
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('view_consumption')
                ->label(__('admin.view_consumption_history'))
                ->icon('heroicon-o-arrow-trending-down')
                ->color('info')
                ->url(fn () => BranchResource::getUrl('view', ['record' => $this->record]) . '?activeRelationManager=0&activeTab=materialConsumptions')
                ->openUrlInNewTab(false),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(1)
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make(__('strings.branch_details'))
                                    ->columns(3)
                                    ->schema([
                                        ImageEntry::make('image')
                                            ->label(__('strings.branch_image'))
                                            ->circular(),
                                             

                                        TextEntry::make('code')
                                            ->label('ID'),

                                        TextEntry::make('name')
                                            ->label(__('strings.branch_name')),

                                        TextEntry::make('description')
                                            ->label(__('strings.branch_description')),
 
                                        TextEntry::make('scope_work')
                                            ->label(__('strings.scope_work')),


                                              TextEntry::make('opening_date')
                                            ->label(__('strings.opening_date')),

                                        TextEntry::make('close_date')
                                            ->label(__('strings.close_date')),
                                        TextEntry::make('phone_number')
                                            ->label(__('strings.phone_number')),
                                    ]),
                            ]),

                   
                           Grid::make(2)
                           
                                
                                    ->schema([
                                        RepeatableEntry::make('branchMaterial')
                                        ->label(__('admin.materail_branch'))
                                            ->schema([
                                                TextEntry::make('material.name')
                                                    ->label(__('admin.material_name')),
                                                TextEntry::make('quantity_in_stock')
                                                    ->label(__('admin.quantity_in_stock')),
                                                TextEntry::make('unit')
                                                    ->label(__('admin.unit'))
                                                    ->columnSpan(2),
                                            ])
                                            ->columns(2),
                                   
                            ]),
                    ]),
            ]);
    }
}
