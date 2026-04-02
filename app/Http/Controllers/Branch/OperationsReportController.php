<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationsReportController extends Controller
{
    use ApiResponse;

    public function operationsReports(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->branch_id) {
            return $this->errorResponse('Branch not found for this user.', 404);
        }

        $branchId = $user->branch_id;
        $dateFilter = $request->input('date_filter', 'today'); 
        $dateRange = $this->getDateRange($dateFilter);

        // Current period query
        $currentOrders = Order::where('branch_id', $branchId)
             ->where('status', 'completed');

        $currentMetrics = $this->calculateOperationsMetrics($currentOrders);

        // Yesterday metrics for comparison
        $yesterdayRange = $this->getDateRange('yesterday');
        $yesterdayOrders = Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$yesterdayRange['start'], $yesterdayRange['end']])
            ->where('status', 'completed');

        $yesterdayMetrics = $this->calculateOperationsMetrics($yesterdayOrders);

        // Products Performance
        $productsPerformance = $this->calculateProductsPerformance($currentOrders, $branchId);

        // Calculate comparisons
        $totalSalesChange = $this->calculatePercentageChange($yesterdayMetrics['total_sales'], $currentMetrics['total_sales']);
        $ordersChange = $currentMetrics['orders_count'] - $yesterdayMetrics['orders_count'];
        $avgOrderChange = $this->calculatePercentageChange($yesterdayMetrics['avg_order'], $currentMetrics['avg_order']);

        return $this->successResponse('Operations reports retrieved successfully', [
            'kpis' => [
                'total_sales' => [
                    'value' => round($currentMetrics['total_sales'], 2),
                    'change_yesterday' => round($totalSalesChange, 2),
                ],
                'orders' => [
                    'value' => $currentMetrics['orders_count'],
                    'change_yesterday' => $ordersChange,
                ],
                'avg_order' => [
                    'value' => round($currentMetrics['avg_order'], 2),
                    'change_yesterday' => round($avgOrderChange, 2),
                ],
                'peak_hour' => [
                    'value' => $currentMetrics['peak_hour'],
                    'yesterday' => $yesterdayMetrics['peak_hour'],
                ],
                'lowest_hour' => [
                    'value' => $currentMetrics['lowest_hour'],
                    'yesterday' => $yesterdayMetrics['lowest_hour'],
                ],
            ],
            'products_performance' => $productsPerformance,
            'date_filter' => $dateFilter,
            'date_range' => [
                'start' => $dateRange['start']->toDateString(),
                'end' => $dateRange['end']->toDateString(),
            ],
        ]);
    }

    private function calculateOperationsMetrics($ordersQuery): array
    {
        $orders = $ordersQuery->get();
        
        $totalSales = $orders->sum('total_price') ?? 0;
        $ordersCount = $orders->count();
        $avgOrder = $ordersCount > 0 ? ($totalSales / $ordersCount) : 0;

        // Calculate peak and lowest hours
        $hourlyOrders = $orders->groupBy(function ($order) {
            return Carbon::parse($order->created_at)->format('G'); // Hour in 24-hour format (0-23)
        })->map->count();

        $peakHour = $hourlyOrders->isNotEmpty() 
            ? Carbon::createFromTime($hourlyOrders->keys()->max(), 0, 0)->format('g A')
            : 'N/A';

        $lowestHour = $hourlyOrders->isNotEmpty() 
            ? Carbon::createFromTime($hourlyOrders->keys()->min(), 0, 0)->format('g A')
            : 'N/A';

        return [
            'total_sales' => $totalSales,
            'orders_count' => $ordersCount,
            'avg_order' => $avgOrder,
            'peak_hour' => $peakHour,
            'lowest_hour' => $lowestHour,
        ];
    }

    private function calculateProductsPerformance($ordersQuery, int $branchId): array
    {
        $orderIds = $ordersQuery->pluck('id')->toArray();

        if (empty($orderIds)) {
            return [];
        }

        // Get orders with their items and product information
        $orders = Order::whereIn('id', $orderIds)
            ->with(['items.product'])
            ->get();

        // Get all order items grouped by product
        $productsData = collect();
        
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $productId = $item->product_id;
                $productName = $item->product ? $item->product->name : 'Unknown Product';
                
                if (!$productsData->has($productId)) {
                    $productsData->put($productId, [
                        'product_id' => $productId,
                        'product_name' => $productName,
                        'order_ids' => collect(),
                        'sales' => 0,
                        'in_store_order_ids' => collect(),
                        'delivery_order_ids' => collect(),
                        'in_store_quantity' => 0,
                        'delivery_quantity' => 0,
                    ]);
                }
                
                $productData = $productsData->get($productId);
                $productData['order_ids']->push($order->id);
                $productData['sales'] += $item->total_price;
                
                // Track quantities by order type (In Store = pick_up, Delivery = delivery)
                if ($order->type === 'pick_up') {
                    $productData['in_store_order_ids']->push($order->id);
                    $productData['in_store_quantity'] = ($productData['in_store_quantity'] ?? 0) + $item->quantity;
                } elseif ($order->type === 'delivery') {
                    $productData['delivery_order_ids']->push($order->id);
                    $productData['delivery_quantity'] = ($productData['delivery_quantity'] ?? 0) + $item->quantity;
                }
                
                $productsData->put($productId, $productData);
            }
        }

        // Transform and calculate rates
        $totalSales = $productsData->sum('sales');

        $productsPerformance = $productsData->map(function ($product) use ($totalSales) {
            $uniqueOrders = $product['order_ids']->unique()->count();
            $rate = $totalSales > 0 ? round(($product['sales'] / $totalSales) * 100, 2) : 0;
            
            return [
                'product_id' => $product['product_id'],
                'product_name' => $product['product_name'],
                'product' => $product['product_name'], // Keep for backward compatibility
                'order_ids' => $product['order_ids']->unique()->values()->all(),
                'sales' => round($product['sales'], 2),
                'rate' => $rate,
                'orders' => $uniqueOrders,
                'in_store_order_ids' => $product['in_store_order_ids']->unique()->values()->all(),
                'delivery_order_ids' => $product['delivery_order_ids']->unique()->values()->all(),
                'in_store_quantity' => (int) ($product['in_store_quantity'] ?? 0),
                'delivery_quantity' => (int) ($product['delivery_quantity'] ?? 0),
                'in_store' => (int) ($product['in_store_quantity'] ?? 0), // Keep for backward compatibility
                'delivery' => (int) ($product['delivery_quantity'] ?? 0), // Keep for backward compatibility
            ];
        })->sortByDesc('sales')->values()->all();

        return $productsPerformance;
    }

    private function getDateRange(string $filter): array
    {
        $now = Carbon::now();

        return match ($filter) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'this_week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ],
            'this_month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            'yesterday' => [
                'start' => $now->copy()->subDay()->startOfDay(),
                'end' => $now->copy()->subDay()->endOfDay(),
            ],
            default => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
        };
    }

    private function calculatePercentageChange(float $oldValue, float $newValue): float
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }

        return round((($newValue - $oldValue) / $oldValue) * 100, 2);
    }
}
