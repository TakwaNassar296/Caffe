<?php

namespace App\Http\Controllers\Api\Driver;


use App\Models\Order;
use App\Models\DriverOrder;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Notifications\NotificationUser;
use App\Http\Resources\Api\OrderResource;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    use ApiResponse;


    // get new orders where orders not booked by  any driver
    public function newOrder(Request $request)
    {
        $driver = auth('driver')->user();


        $activeStatuses = ['pending', 'in_preparation', 'prepared', 'under_review'];

        $ordersBranch = Order::where('booked_by_driver', 0)
            ->whereIn('status', $activeStatuses)
            ->whereIn('branch_id', $driver->branches()->pluck('branches.id'));

        if ($request->filled('order_num')) {
            $ordersBranch->where('order_num', 'like', '%' . $request->order_num . '%');
        }

        $ordersBranch = $ordersBranch->paginate(10);

        if ($ordersBranch->isEmpty()) {
            return $this->successResponse(__('apis.no_new_orders'), []);
        }

        return $this->PaginationResponse(OrderResource::collection($ordersBranch), __('apis.new_orders_retrieved'), 200);
    }


    // book order by driver

    public function bookOrder($orderID)
    {
        $driver = auth('driver')->user();

        $order = Order::where('id', $orderID)
            ->whereIn('branch_id', $driver->branches()->pluck('branches.id'))
            ->first();

        if (!$order) {
            return $this->successResponse(__('apis.order_inaccessible'), []);
        }

        DB::beginTransaction();
        try {
            // DriverOrder::create([
            //     'driver_id' => $driver->id,
            //     'order_id' => $order->id,
            //     'branch_id' => $order->branch_id,
            // ]);

            $order->update([
                // 'status' => 'shipped',
                'booked_by_driver' => 1,
                'driver_id' => $driver->id,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(__('apis.failed_confirm_order'), $e->getMessage());
        }

        return $this->successResponse(__('apis.order_confirmed'), new OrderResource($order));
    }


    // receive order by driver 

    public function receiveOrderBranch($orderID)
    {
        $driver = auth('driver')->user();

        $order = Order::where('id', $orderID)
            ->where('booked_by_driver', 1)
            ->whereIn('branch_id', $driver->branches()->pluck('branches.id'))
            ->first();

        if (!$order) {
            return $this->successResponse(__('apis.order_inaccessible'), []);
        }

        DB::beginTransaction();
        try {
            $order->update([
                'status' => 'shipped',
                'driver_id' => $driver->id,
            ]);

            Notification::send($order->user, new NotificationUser($order, "Your order #{$order->id} is on the way!"));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(__('apis.failed_update_order'), $e->getMessage());
        }

        return $this->successResponse(__('apis.order_shipped'), new OrderResource($order));
    }

    // get orders on delivery where orders status is pending or shipped
    public function onDelivery(Request $request)
    {
        $orders = Order::whereIn('status', ['pending', 'shipped'])->where('driver_id', auth('driver')->id())->when($request->filled('order_num'), function ($q) use ($request) {
            $q->where('order_num', 'like', '%' . $request->order_num . '%');
        })->paginate(10);

        if ($orders->isEmpty()) {
            return $this->successResponse(__('apis.no_orders_delivery'));
        }


        return $this->PaginationResponse(OrderResource::collection($orders), __('apis.orders_delivery_retrieved'), 200);
    }


    // get delivered orders where orders status is completed
    public function delivered(Request $request)
    {
        $orders = Order::where('status', 'completed')->where('driver_id', auth('driver')->id())->when($request->filled('order_num'), function ($q) use ($request) {
            $q->where('order_num', 'like', '%' . $request->order_num . '%');
        })->paginate(10);

        if ($orders->isEmpty()) {
            return $this->successResponse(__('apis.no_delivered_orders'), []);
        }

        return $this->PaginationResponse(OrderResource::collection($orders), __('apis.delivered_orders_retrieved'), 200);
    }


    // get cancelled orders where orders status is cancelled

    public function cancelled(Request $request)
    {
        $ordersBranch = Order::where('status', 'cancelled')->where('driver_id', auth('driver')->id())->when($request->filled('order_num'), function ($q) use ($request) {
            $q->where('order_num', 'like', '%' . $request->order_num . '%');
        })->paginate(10);

        if ($ordersBranch->isEmpty()) {
            return $this->successResponse(__('apis.no_cancelled_orders'), []);
        }
        return $this->PaginationResponse(OrderResource::collection($ordersBranch), __('apis.cancelled_orders_retrieved'));
    }


    // verify order by driver
    public function verify(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $order = Order::where('qr_token', $request->token)->first();

        if (! $order) {
            return $this->errorResponse(__('apis.invalid_qr'), 400);
        }

        if ($order->user_id !== auth('driver')->id()) {
            return $this->errorResponse(__('apis.order_not_assigned'), 403);
        }

        if ($order->status === 'completed' || $order->status === 'cancelled') {
            return $this->errorResponse(__('apis.order_already_completed'), 400);
        }

        $order->update([
            'status'      => 'completed',
            // 'verified_at' => now(), // 
        ]);

        return $this->successResponse(__('apis.order_completed'), [
            'order_id' => $order->id,
            'user_id'  => $order->user_id,
        ]);
    }
}
