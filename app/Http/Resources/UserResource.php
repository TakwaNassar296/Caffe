<?php

namespace App\Http\Resources;

use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
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
                'country_key' => $normalizedRequestKey !== '+' ? $normalizedRequestKey : null,
                'phone_number' => $localPhone,
            ];
        }

        return
            [
                
                'id' => $this->id,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'country_key' => $phone['country_key'],
                'phone_number' => $phone['phone_number'],
                'image' => $this->image ? asset('storage/' . $this->image) : asset('images/default.png'),
                'total_points' => $this->total_points ?? 0,
                'total_stars' => $this->total_stars ?? 0,
                'access_token' => $this->createToken('access_token')->plainTextToken,
            ];
    }
}
