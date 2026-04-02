<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    use ApiResponse;

    public function completedOrders(Request $request)
    {
        // check this code correctly 
        $driver = auth('driver')->user();

        $orders = $driver->orders()
            ->where('status', 'completed')
            ->when($request->filled('order_num'), fn($q) => $q->where('order_num', 'like', '%' . $request->order_num . '%'))
            ->paginate(10);

      //  if ($orders->isEmpty()) {
      //      return $this->successResponse(__('apis.no_completed_orders'), []);
      //  }

        $total_driver_finance = $driver->orders()
            ->where('status', 'completed')
            ->sum('driver_finance') ?? 0;

        $compared_month = $this->getComparedMonthData($driver);

        $message = $orders->isEmpty() 
            ? "You do not have any completed orders yet" 
            : __('apis.completed_orders_retrieved');

        $response = $this->PaginationResponse(
            $orders,
           $message
        );

        $responseData = $response->getData(true);

        $responseData['data'] = [
            'orders' => OrderResource::collection($orders),
            'total_driver_finance' => $total_driver_finance,
            'compared_month' => $compared_month,
        ];

        return response()->json($responseData, 200);
    }

    private function getComparedMonthData($driver)
    {
        $currentMonth = now()->month;
        $previousMonth = now()->subMonth()->month;

        $currentMonthFinance = $driver->orders()
            ->where('status', 'completed')
            ->whereMonth('created_at', $currentMonth)
            ->sum('driver_finance');

        $previousMonthFinance = $driver->orders()
            ->where('status', 'completed')
            ->whereMonth('created_at', $previousMonth)
            ->sum('driver_finance');

        $difference = $currentMonthFinance - $previousMonthFinance;

        if ($previousMonthFinance == 0) {
            $percentage = $currentMonthFinance > 0 ? 100 : 0;
        } else {
            $percentage = ($difference / $previousMonthFinance) * 100;
        }

        return [
            'increase' => round($difference, 2),   // الزيادة/النقصان بالقيمة
            'percentage' => round($percentage, 2),   // النسبة المئوية
        ];
    }

    public function cancelledOrders(Request $request)
    {
        $driver = auth('driver')->user();

        $orders = $driver->orders()
            ->where('status', 'canceled')
            ->when($request->filled('order_num'), function ($q) use ($request) {
                $q->where('order_num', 'like', '%' . $request->order_num . '%');
            })
            ->latest()
            ->paginate(10);

        if ($orders->isEmpty()) {
            return $this->successResponse(__('apis.no_cancelled_orders'), []);
        }

        return $this->PaginationResponse(OrderResource::collection($orders), __('apis.cancelled_orders_retrieved'));
    }

    public function historyOrders(Request $request)
    {
        $driver = auth('driver')->user();

        $orders = $driver->orders()
            ->whereIn('status', ['completed', 'canceled'])
            ->when($request->filled('order_num'), function ($q) use ($request) {
                $q->where('order_num', 'like', '%' . $request->order_num . '%');
            })
            ->latest()
            ->paginate(10);

        if ($orders->isEmpty()) {
            return $this->successResponse(__('apis.no_history_orders'), []);
        }

        return $this->PaginationResponse(OrderResource::collection($orders), __('apis.history_orders_retrieved'));
    }
}
//php artisan make:migration add_app_link_to_users_table --table=users
