<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponse;

    public function financialReports(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->branch_id) {
            return $this->errorResponse('Branch not found for this user.', 404);
        }

        $dateFilter = $request->input('date_filter', 'today');
        $dateRange = $this->getDateRange($dateFilter);

        $currentQuery = $this->getCompletedOrdersQuery(
            $user->branch_id,
            $dateRange['start'],
            $dateRange['end']
        );

        $currentMetrics = $this->calculateOrdersMetrics($currentQuery);

        $yesterday = $this->getDateRange('yesterday');
        $yesterdayMetrics = $this->calculateOrdersMetrics(
            $this->getCompletedOrdersQuery($user->branch_id, $yesterday['start'], $yesterday['end'])
        );

        $lastMonthStart = \Carbon\Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = \Carbon\Carbon::now()->subMonth()->endOfMonth();
        $lastMonthMetrics = $this->calculateOrdersMetrics(
            $this->getCompletedOrdersQuery($user->branch_id, $lastMonthStart, $lastMonthEnd)
        );

        $compareYesterday = $this->calculateComparisonMetrics($currentMetrics, $yesterdayMetrics);
        $compareLastMonth = $this->calculateComparisonMetrics($currentMetrics, $lastMonthMetrics);

        $materials = \App\Models\BranchMaterial::where('branch_id', $user->branch_id)
            ->whereHas('material', function ($query) {
                $query->where('material_type', 'internal');
            })
            ->with('material')
            ->get();

        $dailySettlement = $materials->map(function ($item) {
            $expected = (float) ($item->current_quantity ?? 0);
            $actual = (float) ($item->actual_quantity ?? 0);
            return [
                'name' => $item->material?->name,
                'expected_qty' => number_format($expected, 2),
                'actual_qty' => number_format($actual, 2),
                'difference' => number_format($actual - $expected, 2),
                'unit' => $item->material?->unit,
                'status' => $expected > 0 ? 'good' : 'out_of_stock'
            ];
        });

        return $this->successResponse('Reports retrieved successfully', [
            'kpis' => [
                'sales_amount' => [
                    'value' => round($currentMetrics['sales_amount'], 2),
                    'change_yesterday' => (string) $compareYesterday['sales_change'],
                    'change_last_month' => (string) $compareLastMonth['sales_change'],
                ],
                'net_revenue' => [
                    'value' => round($currentMetrics['net_revenue'], 2),
                    'change_yesterday' => (string) $compareYesterday['net_change'],
                    'change_last_month' => (string) $compareLastMonth['net_change'],
                ],
                'orders_count' => [
                    'value' => $currentMetrics['orders_count'],
                    'change_yesterday' => (string) $compareYesterday['orders_change'],
                ],
                'total_discounts' => [
                    'value' => round($currentMetrics['discount_amount'], 2),
                    'change_yesterday' => (string) $compareYesterday['discount_change'],
                ],
                'avg_order' => [
                    'value' => round($currentMetrics['avg_after_discount'], 2),
                    'change_yesterday' => (string) $compareYesterday['avg_after_change'],
                ],
            ],
            'daily_settlement' => $dailySettlement,
            'statistics_details' => [
                array_merge(['source' => 'Delivery'],
                    $this->calculateSourceStatistics($currentQuery, 'delivery')
                ),
                array_merge(['source' => 'In Store'],
                    $this->calculateSourceStatistics($currentQuery, 'pick_up')
                ),
            ],
            'date_filter' => $dateFilter,
        ]);
    }


    private function calculateSourceStatistics($ordersQuery, string $type): array
    {
        $orders = (clone $ordersQuery)->where('type', $type)->get();

        $sales = $orders->sum('total_price');
        $discount = $orders->sum('discount');

        return [
            'total_sales' => round($sales, 2),
            'net_income' => round($sales - $discount, 2),
            'discount_percent' => $sales > 0 ? round(($discount / $sales) * 100, 2) : 0,
            'orders' => $orders->count(),
            'avg_before_discount' => round($orders->avg('sub_total') ?? 0, 2),
            'avg_after_discount' => round($orders->avg('total_price') ?? 0, 2),
        ];
    }


    private function calculateComparisonMetrics(array $current, array $previous): array
    {
        return [
            'sales_change' => $this->calculatePercentageChange(
                $previous['sales_amount'],
                $current['sales_amount']
            ),
            'net_change' => $this->calculatePercentageChange(
                $previous['net_revenue'],
                $current['net_revenue']
            ),
            'discount_change' => $this->calculatePercentageChange(
                $previous['discount_amount'],
                $current['discount_amount']
            ),
            'orders_change' => $previous['orders_count'] - $current['orders_count'],
            'avg_before_change' => $this->calculatePercentageChange(
                $previous['avg_before_discount'],
                $current['avg_before_discount']
            ),
            'avg_after_change' => $this->calculatePercentageChange(
                $previous['avg_after_discount'],
                $current['avg_after_discount']
            ),
        ];
    }



    private function calculateOrdersMetrics($ordersQuery): array
    {
        $orders = $ordersQuery->get();

        $sales = $orders->sum('total_price');
        $discount = $orders->sum('discount');
        $net = $sales - $discount;
        $count = $orders->count();

        return [
            'sales_amount' => $sales,
            'discount_amount' => $discount,
            'net_revenue' => $net,
            'orders_count' => $count,
            'avg_before_discount' => $orders->avg('sub_total') ?? 0,
            'avg_after_discount' => $orders->avg('total_price') ?? 0,
        ];
    }



    private function getCompletedOrdersQuery(int $branchId, Carbon $start, Carbon $end)
    {
        return Order::where('branch_id', $branchId)
            
            ->where('status', 'completed');
    }


        /**
         * Get date range based on filter type
         */
        private function getDateRange(string $filter): array
        {
            $now = Carbon::now();
            
            return match ($filter) {
                'today' => [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ],
                'yesterday' => [
                    'start' => $now->copy()->subDay()->startOfDay(),
                    'end' => $now->copy()->subDay()->endOfDay(),
                ],
                'this_week' => [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                ],
                'this_month' => [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                ],
                'this_year' => [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear(),
                ],
                default => [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ],
            };
        }

        /**
         * Calculate percentage change between two values
         */
        private function calculatePercentageChange(float $oldValue, float $newValue): float
        {
            if ($oldValue == 0) {
                return $newValue > 0 ? 100 : 0;
            }
            
            return round((($newValue - $oldValue) / $oldValue) * 100, 2);
        }
    }
