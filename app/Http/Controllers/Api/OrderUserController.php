<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderResource;
use App\Traits\ApiResponse;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Auth;

class OrderUserController extends Controller
{
    use ApiResponse;

    public function getOrdersByStatus($status)
    {
        $user = Auth::guard('user')->user();

        $statuses = match ($status) {
            'pending' => [
                'pending',
                'under_receipt',
                'under_review',
                'in_preparation',
                'prepared',
                'shipped',
                'arrived',
            ],
            'canceled' => ['canceled'],
            'completed' => ['completed'],
            default => null,
        };

        if ($statuses === null) {
            return $this->errorResponse('Invalid order status', 422);
        }

        $orders = $user->orders()
            ->whereIn('status', $statuses)
            ->latest()
            ->get();

        return $this->successResponse(
            "Orders with status: $status",
            OrderResource::collection($orders)
        );
    }

    public function show($id)
    {
        $user = Auth::guard('user')->user();
        $order = $user->orders()->where('id', $id)->first();

        if (! $order) {
            return $this->errorResponse('Order Not Found');
        }

        if (empty($order->qr_token)) {
            $order->qr_token = \Illuminate\Support\Str::uuid()->toString();
            $order->save();
        }

        $qrUrl = url('api/orders/verify') . '?' . http_build_query([
            'token' => $order->qr_token,
            'order_id' => $order->id,
        ]);

        $qrCode = new QrCode(
            data: $qrUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255),
        );

        $writer = new PngWriter;
        $result = $writer->write($qrCode);

        $qrDataUri = $result->getDataUri();

        // $shouldRate = $order->status === 'completed' && ! $order?->review()->exists();
        $shouldRate = $order->status === 'completed';

        return $this->successResponse('Order', [
            'order' => new OrderResource($order),
            'qr_url' => $qrUrl,
            'qr_data_uri' => $qrDataUri,
            'should_rate' => $shouldRate,
        ]);
    }

    public function cancelOrder($orderId)
    {
        $user = Auth::guard('user')->user();

        $order = $user->orders()->where('id', $orderId)->first();

        if (! $order) {
            return $this->errorResponse('Order not found', 404);
        }

        if ($order->status == 'completed') {
            return $this->errorResponse('Completed orders cannot be canceled', 422);
        }

        $order->update(['status' => 'canceled']);

        return $this->successResponse('Order canceled successfully', new OrderResource($order));
    }
}
