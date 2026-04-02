<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationAdmin extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $order;
    protected $message;
    protected $title;

    public function __construct(Order $order = null, $message = null, $title = null)
    {
        $this->order = $order;
        $this->message = $message;
        $this->title = $title;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        if ($this->order) {
            return [
                'order_id'   => $this->order->id,
                'branch_id'  => $this->order->branch_id,
                'title' => $this->title ?? str_replace(':branch', $this->order->branch->name ?? '', __('strings.new_order_received')),
                'message' => $this->message ?? str_replace(':branch', $this->order->branch->name ?? '', __('strings.new_order_received')),
            ];
        }

        return [
            'title' => $this->title ?? __('admin.notification'),
            'message' => $this->message ?? '',
        ];
    }
}

