<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_all_as_read')
                ->label(__('admin.mark_all_as_read'))
                ->icon('heroicon-o-check-circle')
                ->action(function () {
                    $admin = auth()->guard('admin')->user();
                    $admin->unreadNotifications->markAsRead();
                })
                ->visible(fn () => auth()->guard('admin')->user()->unreadNotifications->isNotEmpty()),
        ];
    }
}

