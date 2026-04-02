<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Filament\Resources\OrderResource\Pages\ListOrders;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOrder extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListOrders::class;
    }

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();

        $statuses = [
            'pending',
            'shipped',
            'completed',
        ];


        $stats = [];

        foreach ($statuses as $status) {
            $stats[] = Stat::make(
                __('strings.' . $status),
                $query->clone()->where('status', $status)->count()
            )->chart(
                $query->clone()
                    ->where('status', $status)
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as aggregate')
                    ->groupBy('date')
                    ->reorder()
                    ->orderBy('date', 'asc')
                    ->pluck('aggregate')
                    ->toArray()
            );
        }


        $stats[] = Stat::make(
            __('strings.average_price'),
            number_format((float) $query->avg('total_price'), 2)
        );

        return $stats;
    }
}
