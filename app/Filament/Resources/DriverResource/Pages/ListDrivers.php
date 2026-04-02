<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use App\Models\Driver;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDrivers extends ListRecords
{
    protected static string $resource = DriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
             ->badge(Driver::query()->count())
                ->icon('heroicon-m-bars-3'),

            'pending' => Tab::make()
                ->badge(Driver::query()->where('status', 'pending')->count())
                ->icon('heroicon-m-exclamation-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending')),

            'accepted' => Tab::make()
                   ->badge(Driver::query()->where('status', 'accepted')->count())
                ->icon('heroicon-m-arrow-trending-up')
                ->modifyQueryUsing(fn(Builder $query) =>  $query->where('status', 'accepted')),

            'rejected' => Tab::make()
                 ->badge(Driver::query()->where('status', 'rejected')->count())
                ->icon('heroicon-m-x-mark')
                ->modifyQueryUsing(fn(Builder $query) =>  $query->where('status', 'rejected')),
        ];
    }
}
