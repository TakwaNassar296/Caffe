<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    use ExposesTableToWidgets;

    protected function getActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return OrderResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('strings.all'))
                ->query(fn () => $this->getTableQuery())
                ->badge(fn () => $this->getTableQuery()->count()),

            'pending' => Tab::make(__('strings.pending'))
                ->query(fn () => $this->getTableQuery()->where('status', 'pending'))
                ->badge(fn () => $this->getTableQuery()->where('status', 'pending')->count()),

            'under_receipt' => Tab::make(__('strings.under_receipt'))
                ->query(fn () => $this->getTableQuery()->where('status', 'under_receipt'))
                ->badge(fn () => $this->getTableQuery()->where('status', 'under_receipt')->count()),

            'under_review' => Tab::make(__('strings.under_review'))
                ->query(fn () => $this->getTableQuery()->where('status', 'under_review'))
                ->badge(fn () => $this->getTableQuery()->where('status', 'under_review')->count()),

            'in_preparation' => Tab::make(__('strings.in_preparation'))
                ->query(fn () => $this->getTableQuery()->where('status', 'in_preparation'))
                ->badge(fn () => $this->getTableQuery()->where('status', 'in_preparation')->count()),

            'prepared' => Tab::make(__('strings.prepared'))
                ->query(fn () => $this->getTableQuery()->where('status', 'prepared'))
                ->badge(fn () => $this->getTableQuery()->where('status', 'prepared')->count()),

            'shipped' => Tab::make(__('strings.shipped'))
                ->query(fn () => $this->getTableQuery()->where('status', 'shipped'))
                ->badge(fn () => $this->getTableQuery()->where('status', 'shipped')->count()),

            'arrived' => Tab::make(__('strings.arrived'))
                ->query(fn () => $this->getTableQuery()->where('status', 'arrived'))
                ->badge(fn () => $this->getTableQuery()->where('status', 'arrived')->count()),

            'canceled' => Tab::make(__('strings.canceled'))
                ->query(fn () => $this->getTableQuery()->where('status', 'canceled'))
                ->badge(fn () => $this->getTableQuery()->where('status', 'canceled')->count()),

            'completed' => Tab::make(__('strings.completed'))
                ->query(fn () => $this->getTableQuery()->where('status', 'completed'))
                ->badge(fn () => $this->getTableQuery()->where('status', 'completed')->count()),
        ];
    }
}
