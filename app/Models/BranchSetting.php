<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        // Peak Hours
        'peak_start_time',
        'peak_end_time',
        'enable_peak_pricing',
        // Order Number Format
        'order_prefix',
        'starting_number',
        'max_orders_per_hour',
        // Printer Settings
        'auto_print_orders',
        'printer_name',
        'receipt_format',
        'print_kitchen_copy',
        'print_customer_copy',
        // Notification Settings
        'order_sound_alert',
        'mobile_notifications',
        'email_notifications',
        'low_stock_alerts',
        // Inventory Settings
        'auto_deduction',
        'minimum_stock_alert_level',
        'auto_ordering',
        // Online Order Settings
        'enable_online_orders',
        'preparation_time',
        'auto_accept_orders',
        'delivery_integration',
    ];

    protected $casts = [
        'peak_start_time' => 'datetime:H:i',
        'peak_end_time' => 'datetime:H:i',
        'enable_peak_pricing' => 'boolean',
        'starting_number' => 'integer',
        'max_orders_per_hour' => 'integer',
        'auto_print_orders' => 'boolean',
        'print_kitchen_copy' => 'boolean',
        'print_customer_copy' => 'boolean',
        'order_sound_alert' => 'boolean',
        'mobile_notifications' => 'boolean',
        'email_notifications' => 'boolean',
        'low_stock_alerts' => 'boolean',
        'auto_deduction' => 'boolean',
        'minimum_stock_alert_level' => 'integer',
        'auto_ordering' => 'boolean',
        'enable_online_orders' => 'boolean',
        'preparation_time' => 'integer',
        'auto_accept_orders' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
