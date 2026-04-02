<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductsReportController extends Controller
{
    use ApiResponse;

    public function productsReports(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->branch_id) {
            return $this->errorResponse('Branch not found for this user.', 404);
        }

        $branchId   = $user->branch_id;
        $dateFilter = $request->input('date_filter', 'this_year');
        $dateRange  = $this->getDateRange($dateFilter);

        $orderIds = Order::where('branch_id', $branchId)
            ->where('status', 'completed')
             
            ->pluck('id');
         if ($orderIds->isEmpty()) {
            return $this->successResponse('Products reports retrieved successfully', [
                'summary' => [
                    'most_selling'   => null,
                    'least_selling'  => null,
                    'total_products' => 0,
                    'top_category'   => null,
                ],
                'products'    => [],
                'date_filter' => $dateFilter,
                'date_range'  => [
                    'start' => $dateRange['start']->toDateString(),
                    'end'   => $dateRange['end']->toDateString(),
                ],
            ]);
        }

        $productsStats = $this->calculateProductsStatistics($orderIds, $branchId);

        return $this->successResponse('Products reports retrieved successfully', [
            'summary'     => $this->calculateSummary($productsStats),
            'products'    => $this->formatProductsData($productsStats),
            'date_filter' => $dateFilter,
            'date_range'  => [
                'start' => $dateRange['start']->toDateString(),
                'end'   => $dateRange['end']->toDateString(),
            ],
        ]);
    }

    private function calculateProductsStatistics($orderIds, int $branchId): array
    {
        $branchProducts = Product::whereHas('branches', fn ($q) =>
            $q->where('branch_id', $branchId)
        )->pluck('id');

        if ($branchProducts->isEmpty()) {
            return [];
        }

        $orderItems = OrderItem::whereIn('order_id', $orderIds)
            ->whereIn('product_id', $branchProducts)
            ->with(['product.category'])
            ->get();

        $productsData = [];

        foreach ($orderItems as $item) {
            $productId = $item->product_id;

            if (!isset($productsData[$productId])) {
                $productsData[$productId] = [
                    'product_id'     => $productId,
                    'product'        => $item->product,
                    'order_ids'      => collect(),
                    'total_sales'    => 0,
                    'total_quantity' => 0,
                ];
            }

            $productsData[$productId]['order_ids']->push($item->order_id);
            $productsData[$productId]['total_sales']    += $item->total_price;
            $productsData[$productId]['total_quantity'] += $item->quantity;
        }

        foreach ($productsData as &$data) {
            $data['orders_count'] = $data['order_ids']->unique()->count();
            unset($data['order_ids']);
        }

        return $productsData;
    }

    private function calculateSummary(array $productsStats): array
    {
        if (empty($productsStats)) {
            return [
                'most_selling'   => null,
                'least_selling'  => null,
                'total_products' => 0,
                'top_category'   => null,
            ];
        }

        $collection = collect($productsStats);

        $mostSelling  = $collection->sortByDesc('orders_count')->first();
        $leastSelling = $collection->sortBy('orders_count')->first();

        $topCategoryId = $collection
            ->groupBy(fn ($p) => $p['product']->category_id)
            ->map(fn ($items) => collect($items)->sum('total_sales'))
            ->sortDesc()
            ->keys()
            ->first();

        return [
            'most_selling' => [
                'product_id'   => $mostSelling['product_id'],
                'product_name' => $mostSelling['product']->name,
                'orders'       => $mostSelling['orders_count'],
            ],
            'least_selling' => [
                'product_id'   => $leastSelling['product_id'],
                'product_name' => $leastSelling['product']->name,
                'orders'       => $leastSelling['orders_count'],
            ],
            'total_products' => $collection->count(),
            'top_category'   => Category::find($topCategoryId)?->name,
        ];
    }

    private function formatProductsData(array $productsStats): array
    {
        $totalSales = collect($productsStats)->sum('total_sales');

        return collect($productsStats)
            ->map(function ($stat) use ($totalSales) {
                $product = $stat['product'];
                $price   = (float) $product->price;

                $cost   = $price * 0.45;
                $profit = $price - $cost;

                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'category' => $product->category?->name,
                    'orders' => $stat['orders_count'],
                    'price' => round($price, 2),
                    'cost' => round($cost, 2),
                    'profit' => round($profit, 2),
                    'total_sales' => round($stat['total_sales'], 2),
                    'percent_of_total_sales' => $totalSales
                        ? round(($stat['total_sales'] / $totalSales) * 100, 2)
                        : 0,
                ];
            })
            ->sortByDesc('orders')
            ->values()
            ->all();
    }

    private function getDateRange(string $filter): array
    {
        $now = Carbon::now();

        return match ($filter) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end'   => $now->copy()->endOfDay(),
            ],
            'yesterday' => [
                'start' => $now->copy()->subDay()->startOfDay(),
                'end'   => $now->copy()->subDay()->endOfDay(),
            ],
            'this_week' => [
                'start' => $now->copy()->startOfWeek(),
                'end'   => $now->copy()->endOfWeek(),
            ],
            'this_month' => [
                'start' => $now->copy()->startOfMonth(),
                'end'   => $now->copy()->endOfMonth(),
            ],
            'this_year' => [
                'start' => $now->copy()->startOfYear(),
                'end'   => $now->copy()->endOfYear(),
            ],
         
        };
    }
}
