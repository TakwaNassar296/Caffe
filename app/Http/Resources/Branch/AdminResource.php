<?php

namespace App\Http\Resources\Branch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalPoints = (float) ($this->points_sum_point_amount ?? 0);
        
        $target = 50000; 
        $performance = ($totalPoints / $target) * 100;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'total_points' => $totalPoints,
            'orders' => 3,
            'branch_id' => $this->branch_id,
            'performance' => round(min($performance, 100), 2),
            'access_token' => $this->when($request->routeIs('login'), function() {
                return $this->createToken('access_token')->plainTextToken;
            }),
        ];
    }
}