<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class OrdersStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $totalOrders = Order::count();
        $todayOrders = Order::whereDate('created_at', Carbon::today())->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalRevenue = Order::sum('total_price');
        $todayRevenue = Order::whereDate('created_at', Carbon::today())->sum('total_price');
        $paidOrders = Order::where('payment_status', 'paid')->count();

        return [
            Stat::make(__('strings.total_orders'), Number::format($totalOrders))
                ->description(__('strings.all_time_orders'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make(__('strings.today_orders'), Number::format($todayOrders))
                ->description(__('strings.orders_placed_today'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make(__('strings.completed_orders'), Number::format($completedOrders))
                ->description(__('strings.successfully_completed'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('strings.pending_orders'), Number::format($pendingOrders))
                ->description(__('strings.awaiting_processing'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('strings.paid_orders'), Number::format($paidOrders))
                ->description(__('strings.orders_with_paid_status'))
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('success'),

            Stat::make(__('strings.total_revenue'), Number::currency($totalRevenue, 'TRY'))
                ->description(__('strings.all_time_revenue'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make(__('strings.today_revenue'), Number::currency($todayRevenue, 'TRY'))
                ->description(__('strings.revenue_from_today'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }
}

