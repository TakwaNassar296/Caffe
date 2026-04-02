<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class MostUsersOrdersWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 8;

    protected function getTableHeading(): string | Htmlable | null
    {
        return __('strings.most_users_by_orders');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->withCount('orders')
                    ->orderByDesc('orders_count')
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

                Tables\Columns\TextColumn::make('orders_count')
                    ->label(__('strings.total_orders'))
                    ->counts('orders')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_points')
                    ->label(__('strings.points'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_stars')
                    ->label(__('strings.stars'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('strings.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('orders_count', 'desc');
    }
}

