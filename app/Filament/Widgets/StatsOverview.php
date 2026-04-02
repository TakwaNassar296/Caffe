<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
 
        $revenue = Order::sum('total_price');

        $newCustomers = User::whereDate('created_at', Carbon::today())->count();

        $newOrders = Order::whereDate('created_at', Carbon::today())->count();

        return [
            Stat::make(__('strings.total_revenue'), Number::currency($revenue, 'TRY'))
                ->description(__('strings.all_time_revenue'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make(__('strings.new_users_today'), Number::format($newCustomers))
                ->description(__('strings.registered_today'))
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),

            Stat::make(__('strings.today_orders'), Number::format($newOrders))
                ->description(__('strings.orders_placed_today'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),
        ];
    }
}
