<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class UsersStatsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::has('orders')->count();
        $newUsersToday = User::whereDate('created_at', Carbon::today())->count();
        $totalUserOrders = Order::count();
        $totalUserRevenue = Order::sum('total_price');

        return [
            Stat::make(__('strings.total_users'), Number::format($totalUsers))
                ->description(__('strings.all_registered_users'))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make(__('strings.active_users'), Number::format($activeUsers))
                ->description(__('strings.users_with_orders'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('strings.new_users_today'), Number::format($newUsersToday))
                ->description(__('strings.registered_today'))
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('warning'),

            Stat::make(__('strings.total_orders'), Number::format($totalUserOrders))
                ->description(__('strings.all_user_orders'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make(__('strings.total_revenue'), Number::currency($totalUserRevenue, 'TRY'))
                ->description(__('strings.total_revenue_from_all_orders'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}

