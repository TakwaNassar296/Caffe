<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchSetting;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

 
class SettingController extends Controller
{
    use ApiResponse;

    /**
     * Get branch settings
     */
    public function Setting(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->branch_id) {
            return $this->errorResponse('Branch not found for this user.', 404);
        }

        $branch = Branch::with('settings')->find($user->branch_id);

        if (!$branch) {
            return $this->errorResponse('Branch not found.', 404);
        }

        // Create default settings if they don't exist
        if (!$branch->settings) {
            $branch->settings()->create([]);
            $branch->load('settings');
        }

        // Format settings response
        $settings = $branch->settings;
        
        $data = [
                 'id' => $branch->id,
                'name' => $branch->name,
                'phone_number' => $branch->phone_number,
                'opening_time' => $branch->opening_date ? $branch->opening_date->format('H:i') : null,
                'closing_time' => $branch->close_date ? $branch->close_date->format('H:i') : null,
                  // Peak Hours
                'peak_start_time' => $settings->peak_start_time ? $settings->peak_start_time->format('H:i') : null,
                'peak_end_time' => $settings->peak_end_time ? $settings->peak_end_time->format('H:i') : null,
                'enable_peak_pricing' => (bool) $settings->enable_peak_pricing,
                
                // Order Number Format
                'order_prefix' => $settings->order_prefix ?? '',
                'starting_number' => (int) ($settings->starting_number ?? 1),
                'max_orders_per_hour' => $settings->max_orders_per_hour,
                'order_preview' => $this->generateOrderPreview($settings),
                
                // Printer Settings
                'auto_print_orders' => (bool) $settings->auto_print_orders,
                'printer_name' => $settings->printer_name,
                'receipt_format' => $settings->receipt_format ?? 'Standard (80mm)',
                'print_kitchen_copy' => (bool) $settings->print_kitchen_copy,
                'print_customer_copy' => (bool) $settings->print_customer_copy,
                
                // Notification Settings
                'order_sound_alert' => (bool) $settings->order_sound_alert,
                'mobile_notifications' => (bool) $settings->mobile_notifications,
                'email_notifications' => (bool) $settings->email_notifications,
                'low_stock_alerts' => (bool) $settings->low_stock_alerts,
                
                // Inventory Settings
                'auto_deduction' => (bool) $settings->auto_deduction,
                'minimum_stock_alert_level' => (int) ($settings->minimum_stock_alert_level ?? 10),
                'auto_ordering' => (bool) $settings->auto_ordering,
                
                // Online Order Settings
                'enable_online_orders' => (bool) $settings->enable_online_orders,
                'preparation_time' => (int) ($settings->preparation_time ?? 15),
                'auto_accept_orders' => (bool) $settings->auto_accept_orders,
                'delivery_integration' => $settings->delivery_integration ?? 'None',
         ];

        return $this->successResponse(
            'Settings retrieved successfully.',
            $data,
            200
        );
    }
     
    public function updateSettings(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->branch_id) {
            return $this->errorResponse('Branch not found for this user.', 404);
        }

        $branch = Branch::with('settings')->find($user->branch_id);

        if (!$branch) {
            return $this->errorResponse('Branch not found.', 404);
        }

        if (!$branch->settings) {
            $branch->settings()->create([]);
            $branch->load('settings');
        }

        // Update branch info
        $branch->update($request->only(['name', 'phone_number']));

        if ($request->opening_time) {
            $branch->opening_date = $this->parseTime($request->opening_time);
        }

        if ($request->closing_time) {
            $branch->close_date = $this->parseTime($request->closing_time);
        }

        $branch->save();

        $data = $request->only([
            'enable_peak_pricing',
            'order_prefix',
            'starting_number',
            'max_orders_per_hour',
            'auto_print_orders',
            'printer_name',
            'receipt_format',
            'print_kitchen_copy',
            'print_customer_copy',
            'order_sound_alert',
            'mobile_notifications',
            'email_notifications',
            'low_stock_alerts',
            'auto_deduction',
            'minimum_stock_alert_level',
            'auto_ordering',
            'enable_online_orders',
            'preparation_time',
            'auto_accept_orders',
            'delivery_integration',
        ]);

        if ($request->peak_start_time) {
            $data['peak_start_time'] = $this->parseTime($request->peak_start_time);
        }

        if ($request->peak_end_time) {
            $data['peak_end_time'] = $this->parseTime($request->peak_end_time);
        }

        $data['name'] = $branch->name;
        $data['phone_number'] = $branch->phone_number;


        $branch->settings->update($data);

        return $this->successResponse('Settings updated successfully',$data);
    }


    private function parseTime($timeString)
    {
         try {
            if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)?/i', $timeString, $matches)) {
                $hour = (int) $matches[1];
                $minute = (int) $matches[2];
                $ampm = strtoupper($matches[3] ?? '');

                if ($ampm === 'PM' && $hour < 12) {
                    $hour += 12;
                } elseif ($ampm === 'AM' && $hour === 12) {
                    $hour = 0;
                }

                return \Carbon\Carbon::createFromTime($hour, $minute, 0);
            }

            return \Carbon\Carbon::parse($timeString);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function generateOrderPreview($settings)
    {
        $prefix = $settings->order_prefix ?? '';
        $startingNumber = $settings->starting_number ?? 1;
        
        return "Next order will be: {$prefix}" . str_pad($startingNumber, 4, '0', STR_PAD_LEFT);
    }
}