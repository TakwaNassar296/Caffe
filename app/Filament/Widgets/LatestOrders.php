<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class LatestOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 7;

    protected function getTableHeading(): string | Htmlable | null
    {
        return __('strings.daily_orders');
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(OrderResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([

                  TextColumn::make('id')
                    ->label(__('strings.order_id'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('strings.order_date'))
                    ->date()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label(__('strings.branch_name'))
                    ->sortable(),

                TextColumn::make('user.first_name')
                    ->label(__('strings.customer_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('strings.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('strings.' . $state)),

                TextColumn::make('total_price')
                    ->label(__('strings.total_price'))
                    ->money('try')
                    ->sortable(),
            ])
            ->actions([   
                Action::make('view')
                    ->label(__('strings.view'))
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
