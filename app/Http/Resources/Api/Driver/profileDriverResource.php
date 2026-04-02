<?php

namespace App\Http\Resources\Api\Driver;

use App\Support\PhoneNumber;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileDriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $phone = PhoneNumber::split($this->phone_number);
        $requestCountryKey = $request->input('country_key');

        if (is_string($requestCountryKey) && $requestCountryKey !== '') {
            $normalizedRequestKey = '+' . (preg_replace('/\D+/', '', $requestCountryKey) ?? '');
            $normalizedPhone = PhoneNumber::normalize($this->phone_number, null);
            $localPhone = $phone['phone_number'];

            if ($normalizedRequestKey !== '+' && str_starts_with($normalizedPhone, $normalizedRequestKey)) {
                $localPhone = substr($normalizedPhone, strlen($normalizedRequestKey));
            }

            $phone = [
                'country_key'  => $normalizedRequestKey,
               // 'country_key' => $normalizedRequestKey !== '+' ? $normalizedRequestKey : null,
                'phone_number' => $localPhone,
            ];
        }

        $currentMonthOrders = $this->orders()
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        $previousMonth = Carbon::now()->subMonth();
        $previousMonthOrders = $this->orders()
            ->whereYear('created_at', $previousMonth->year)
            ->whereMonth('created_at', $previousMonth->month)
            ->count();

        $range = 0;
        if ($previousMonthOrders > 0) {
            $range = (($currentMonthOrders - $previousMonthOrders) / $previousMonthOrders) * 100;
        } elseif ($currentMonthOrders > 0) {
            $range = 100;
        }

        return [
            'id'           => $this->id,
            'first_name'   => $this->first_name,
            'last_name'    => $this->last_name,
            'vehicle_type' => $this->vehicleType->name ?? null,
            'country_key'  => $phone['country_key'],
            'phone_number' => $phone['phone_number'],
            'total_points' => $this->total_points ?? 0,
            'total_stars'  => $this->total_stars ?? 0,
            'image'        => $this->profile_image
                                ? asset('storage/' . $this->profile_image)
                                : asset('images/default.png'),
            'range'        => round($range, 2),
            //   'range'        => 37,
              'total_orders_completed' => $this->orders()->where('status', 'completed')->count(),
        ];
    }
}
