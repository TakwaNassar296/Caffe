<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BranchesStatsWidget;
use App\Filament\Widgets\LatestOrders;
use App\Filament\Widgets\MostBranchesOrdersWidget;
use App\Filament\Widgets\OrdersStatsWidget;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\UsersStatsWidget;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

class ReportsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public static function getNavigationGroup(): string
    {
        return __('strings.orders');
    }
   
    protected static string $view = 'filament.pages.reports-page';

    public ?string $activeTab = 'overview';

    public static function getNavigationLabel(): string
    {
        return __('admin.reports');
    }

    public function getTitle(): string
    {
        return __('admin.reports');
    }

    public function getFooterWidgets(): array
    {
        return match ($this->activeTab) {
            'orders' => [
                OrdersStatsWidget::class,
                LatestOrders::class,
            ],
            'branches' => [
                BranchesStatsWidget::class,
                MostBranchesOrdersWidget::class,
            ],
            'users' => [
                UsersStatsWidget::class,
            ],
            default => [
                StatsOverview::class,
                LatestOrders::class,
            ],
        };
    }

    public function getFooterWidgetsColumns(): int | string | array
    {
        return 4;
    }
}
