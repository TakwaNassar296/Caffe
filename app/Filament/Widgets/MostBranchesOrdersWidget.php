<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class MostBranchesOrdersWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 6;

    protected function getTableHeading(): string | Htmlable | null
    {
        return __('strings.most_branches_by_orders');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Branch::query()
                    ->withCount('orders')
                    ->orderByDesc('orders_count')
            )
            ->defaultPaginationPageOption(10)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('strings.branch_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label(__('strings.code'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label(__('strings.total_orders'))
                    ->counts('orders')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label(__('strings.phone_number'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('strings.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('orders_count', 'desc');
    }
}

