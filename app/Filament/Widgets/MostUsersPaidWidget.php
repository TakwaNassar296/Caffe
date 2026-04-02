<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class MostUsersPaidWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 8;

    protected function getTableHeading(): string | Htmlable | null
    {
        return __('strings.most_users_by_paid_amount');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->withSum('orders', 'total_price')
                    ->withCount('orders')
                    ->orderByDesc('orders_sum_total_price')
            )
            ->defaultPaginationPageOption(10)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('strings.first_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_name')
                    ->label(__('strings.last_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('strings.full_name'))
                    ->getStateUsing(fn (User $record): string => $record->first_name . ' ' . $record->last_name)
                    ->searchable(['first_name', 'last_name']),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label(__('strings.phone_number'))
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

                Tables\Columns\TextColumn::make('total_points')
                    ->label(__('strings.points'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_stars')
                    ->label(__('stars'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('orders_sum_total_price', 'desc');
    }
}

