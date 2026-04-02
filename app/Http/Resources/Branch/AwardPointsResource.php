<?php

namespace App\Http\Resources\Branch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AwardPointsResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'employee_name' => $this->employee ? $this->employee->name : null,
            'point_amount' => $this->point_amount,
            'employee_total_points' => $this->employee ? $this->employee->total_points : null,
            'type_reason' => $this->type_reason,
            'other_reason' => $this->other_reason,
            'notes' => $this->notes,
            'awarded_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
