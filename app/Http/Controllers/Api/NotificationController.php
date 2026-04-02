<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Traits\ApiResponse;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $driver = auth()->user();

        $notifications = $driver->notifications()
            ->latest()
            ->paginate(10);

        $notifications->getCollection()->transform(function ($item) {
            return new NotificationResource($item);
        });

        return $this->PaginationResponse(
            $notifications,
            'All notifications'
        );
    }

    public function unread()
    {

        $driver = auth()->user();

        $notifications = $driver->unreadnotifications()
            ->latest()
            ->paginate(10);

        $notifications->getCollection()->transform(function ($item) {
            return new NotificationResource($item);
        });

        return $this->PaginationResponse(
            $notifications,
            'All notifications'
        );
    }

    public function delete($id)
    {
        $driver = auth()->user();

        $notification = $driver->notifications()->where('id', $id)->first();

        if (! $notification) {
            return $this->errorResponse('Notification not found', 404);
        }

        $notification->delete();

        return $this->successResponse('Notification deleted successfully');
    }

    public function deleteAll()
    {
        $driver = auth()->user();

        $driver->notifications()->delete();

        return $this->successResponse('All notifications deleted successfully');
    }
}
