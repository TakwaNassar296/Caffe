<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use Filament\Infolists\Infolist;
use App\Filament\Resources\UserResource;
use Filament\Infolists\Components\Group;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

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
                 Section::make(__("strings.user_information"))
                    ->schema([

                        TextEntry::make('first_name')->label(__('strings.first_name')),

                        TextEntry::make('last_name') ->label(__('strings.last_name')),

                            ImageEntry::make('image') ->circular()->label(__('strings.image')),

                        TextEntry::make('phone_number') ->label(__('strings.phone_number')),

                        TextEntry::make('total_points')->label(__('strings.total_points')),

                        TextEntry::make('total_stars') ->label(__('strings.total_stars')),

                        TextEntry::make('account_verified_at') ->label(__('strings.account_verified_at')),
                    ])->columns(2),

            ]);
    }
}
