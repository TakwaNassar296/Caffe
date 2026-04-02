<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()   ->visible(fn() => auth()->guard('admin')->user()->super_admin == 1),
        ];
    }

        public function getTabs(): array
    {
        $tabs = [];

      
        $tabs['all'] = Tab::make()
            ->label(__('admin.all'))
            ->icon('heroicon-o-calendar')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query);

      
        $categoryIds = Product::query()->distinct()->pluck('category_id')->toArray();

        $categories = Category::whereIn('id', $categoryIds)->get();

        foreach ($categories as $category) {
            $tabs['category_' . $category->id] = Tab::make()
                ->label($category->name)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category_id', $category->id));
        }

        return $tabs;
    }

}
