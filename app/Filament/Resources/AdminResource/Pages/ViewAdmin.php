<?php

namespace App\Filament\Resources\AdminResource\Pages;

use Filament\Actions;

use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\AdminResource;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;

class ViewAdmin extends ViewRecord
{
    protected static string $resource = AdminResource::class;

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
                Section::make(__("strings.admin_information"))
                    ->schema([
                        TextEntry::make('name')->label(__('strings.name')),
                        TextEntry::make('email')->label(__('strings.email')),
                        TextEntry::make('role')->label(__('strings.role')),
                    
                    ])->columns(2),

            ]);
    }
}
