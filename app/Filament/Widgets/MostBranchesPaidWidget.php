<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class MostBranchesPaidWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 5;

    protected function getTableHeading(): string | Htmlable | null
    {
        return __('strings.most_branches_by_paid_amount');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Branch::query()
                    ->withSum('orders', 'total_price')
                    ->orderByDesc('orders_sum_total_price')
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

                Tables\Columns\TextColumn::make('orders_sum_total_price')
                    ->label(__('strings.total_paid'))
                    ->sum('orders', 'total_price')
                    ->money('egp')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label(__('strings.orders_count'))
                    ->counts('orders')
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label(__('strings.phone_number'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('strings.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('orders_sum_total_price', 'desc');
    }
}

