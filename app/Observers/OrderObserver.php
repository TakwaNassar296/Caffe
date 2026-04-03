<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Admin;
use App\Services\FirebaseNotificationService;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\NotificationAdmin;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function created(Order $order): void
    {
        $this->generateOrderNumber($order);
        $this->notifyAdminsOnNewOrder($order);
    }

    public function updated(Order $order): void
    {
        if ($order->isDirty('status')) {
            $this->notifyUserOfStatusChange($order);

            if ($order->status === 'completed') {
                $this->notifyAdminsOnCompletion($order);
            }
        }

        if ($order->isDirty('status') && $order->status === 'shipped') {
            $this->notifyStaffOnDriverAction($order);
        }
    }

    protected function notifyAdminsOnNewOrder(Order $order): void
    {
        $superAdmins = Admin::role('super_admin')->get();

        if ($superAdmins->isNotEmpty()) {
            foreach ($superAdmins as $admin) {
                FilamentNotification::make()
                    ->title(__('admin.new_order_title'))
                    ->body(__('admin.new_order_body', ['order_num' => $order->order_num]))
                    ->icon('heroicon-o-shopping-cart')
                    ->iconColor('success')
                    ->success()
                    ->sendToDatabase($admin);
            }

            NotificationFacade::send($superAdmins, new NotificationAdmin($order));

            $this->notifyBranchManagers($order);

            Log::info("Admin Notifications Triggered", [
                'order_id' => $order->id,
                'order_num' => $order->order_num,
                'admins_notified_count' => $superAdmins->count()
            ]);
        }
    }

    protected function notifyUserOfStatusChange(Order $order): void
    {
        $user = $order->user;

        if ($user && $user->fcm_token) {
            try {
                $firebaseService = new FirebaseNotificationService();

                $statusText = __("admin.{$order->status}");
                $title = __('admin.order_update_title');
                $body = __('admin.order_update_body', [
                    'order_num' => $order->order_num,
                    'status' => $statusText
                ]);

                $extraData = [
                    'type' => 'order',
                    'order_id' => (string)$order->id,
                    'order_status' => (string)$order->status,
                ];

                Log::info("Attempting to send FCM to User", [
                    'user_id' => $user->id,
                    'token' => $user->fcm_token,
                    'title' => $title,
                    'body' => $body,
                    'payload' => $extraData
                ]);

                $firebaseService->sendNotification($title, $body, $user->fcm_token, false, $extraData);
                
                Log::info("FCM Sent Successfully", ['order_id' => $order->id]);

            } catch (\Exception $e) {
                Log::error("FCM Delivery Failed", [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            Log::warning("FCM Skipped: User has no token", ['order_id' => $order->id]);
        }
    }

    protected function notifyAdminsOnCompletion(Order $order): void
    {
        $superAdmins = Admin::role('super_admin')->get();
        NotificationFacade::send($superAdmins, new NotificationAdmin($order));
        
        Log::info("Completion notification sent to Super Admins", ['order_num' => $order->order_num]);
    }

    protected function generateOrderNumber(Order $order): void
    {
        $date = now()->format('d.m.Y');
        $branch = $order->branch;
        $govCode = $branch?->governorate?->code ?? '00';
        $cityCode = $branch?->city?->code ?? '00';
        $branchNum = $branch?->code ?? '000';
        
        $typeShort = match (strtolower($order->type ?? '')) {
            'delivery' => 'D',
            'pick_up' => 'P',
            default => 'O',
        };

        $order->order_num = "{$date}.{$govCode}.{$cityCode}.{$branchNum}{$typeShort}";
        $order->saveQuietly();
    }


    protected function notifyBranchManagers(Order $order): void
    {
        
        $branchManagers = Admin::where('branch_id', $order->branch_id)
            ->where('role', 'branch_manger') 
            ->whereNotNull('fcm_token')
            ->get();

        if ($branchManagers->isEmpty()) {
            Log::warning("No branch managers found with FCM tokens for branch: {$order->branch_id}");
            return;
        }

        try {
            $firebaseService = new FirebaseNotificationService();
            $title = __('admin.new_order_title');
            $body = __('admin.new_order_body', ['order_num' => $order->order_num]);
            
            $extraData = [
                'type' => 'new_order',
                'order_id' => (string)$order->id,
            ];

            foreach ($branchManagers as $manager) {
                $firebaseService->sendNotification($title, $body, $manager->fcm_token, false, $extraData);
            }

            Log::info("FCM Sent to Branch Managers", [
                'count' => $branchManagers->count(),
                'order_num' => $order->order_num
            ]);

        } catch (\Exception $e) {
            Log::error("FCM Delivery Failed for Branch Manager", [
                'admin_id' => $manager->id, 
                'admin_email' => $manager->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function notifyStaffOnDriverAction(Order $order): void
    {
        $branchManagers = Admin::where('branch_id', $order->branch_id)
            ->where('role', 'branch_manger')
            ->whereNotNull('fcm_token')
            ->get();

        $superAdmins = Admin::role('super_admin')->get();

        $title = __('admin.order_shipped_title');
        $body = __('admin.order_shipped_body', ['order_num' => $order->order_num]);

        $firebaseService = new FirebaseNotificationService();

        foreach ($branchManagers as $manager) {
            try {
                $firebaseService->sendNotification($title, $body, $manager->fcm_token, false, [
                    'type' => 'order_shipped',
                    'order_id' => (string)$order->id
                ]);
            } catch (\Exception $e) {
                Log::error("FCM Failed for Manager {$manager->id}: " . $e->getMessage());
            }
        }

        foreach ($superAdmins as $admin) {
            \Filament\Notifications\Notification::make()
                ->title($title)
                ->body($body)
                ->success()
                ->sendToDatabase($admin);
        }
    }
}