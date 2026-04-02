<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

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
                Section::make(__("admin.employee_information"))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('strings.name')),
                        
                        TextEntry::make('email')
                            ->label(__('strings.email')),
                        
                        TextEntry::make('role')
                            ->label(__('strings.role'))
                            ->badge()
                            ->color('info'),

                        TextEntry::make('total_points')
                            ->label(__('admin.total_points'))
                            ->numeric(decimalPlaces: 2),
                    
                    ])->columns(2),
            ]);
    }
}