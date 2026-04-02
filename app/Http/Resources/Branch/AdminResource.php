<?php

namespace App\Http\Resources\Branch;

use App\Models\EmployeePoint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'total_points' => $this->points->sum('point_amount'),
            'orders' => 3,
            'branch_id' => $this->branch_id,
            'performance'=>24,
            'access_token' => $this->createToken('access_token')->plainTextToken,
        ];
    }
}
