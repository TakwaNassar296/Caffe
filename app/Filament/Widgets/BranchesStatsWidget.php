<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class BranchesStatsWidget extends BaseWidget
{
    protected static ?int $sort =3;

    protected function getStats(): array
    {
        $totalBranches = Branch::count();
        $activeBranches = Branch::has('orders')->count();
        $totalBranchOrders = Order::whereNotNull('branch_id')->count();
        $totalBranchRevenue = Order::whereNotNull('branch_id')->sum('total_price');

        return [
            Stat::make(__('strings.total_branches'), Number::format($totalBranches))
                ->description(__('strings.all_registered_branches'))
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info'),

            Stat::make(__('strings.active_branches'), Number::format($activeBranches))
                ->description(__('strings.branches_with_orders'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('strings.branch_orders'), Number::format($totalBranchOrders))
                ->description(__('strings.total_orders_from_branches'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),

            Stat::make(__('strings.branch_revenue'), Number::currency($totalBranchRevenue, 'TRY'))
                ->description(__('strings.total_revenue_from_branches'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}

