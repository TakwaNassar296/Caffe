<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderResource;
use App\Models\Branch;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use ApiResponse;

  public function orders(Request $request)
{
    $branchId = Auth::user()->branch_id;

    $status = $request->query('status'); 

    $ordersQuery = Order::whereHas('branch', function($query) use ($branchId) {
        $query->where('id', $branchId); 
    });
    

   
    if ($status) {
        $ordersQuery->where('status', $status);
    }

    $orders = $ordersQuery->paginate(10);

    return $this->PaginationResponse(
        OrderResource::collection($orders),
        'Orders retrieved successfully.',
        200
    );
}


    public function changeStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:pending,under_receipt,under_review,in_preparation,prepared,shipped,arrived,completed,canceled,wasted',
            'cancelled_reason' => 'required_if:status,canceled,wasted|nullable|string',
        ]);

        $order = Order::findOrFail($request->order_id);
        $user = auth()->user();
      
        

        $order->update([
            'status' => $request->status,
            'cancelled_reason' => in_array($request->status, ['canceled', 'wasted']) 
                ? $request->cancelled_reason 
                : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => [
                'order_id' => $order->id,
                'invoice_id' => $order->invoice_id,
                'status' => $order->status,
                'cancelled_reason' => $order->cancelled_reason,
            ]
        ]);
    }

   public function show($id)
    {
        $branchId = Auth::user()->branch_id;
        $order = Order::where('id', $id)
            ->where('branch_id', $branchId)
            ->firstOrFail();
    
            return $this->successResponse(
                    'Order retrieved successfully.',
                new OrderResource($order),
                200
            );

    }

}
