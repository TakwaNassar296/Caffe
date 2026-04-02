<?php

namespace App\Http\Resources\Admin\V2\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllUserResource extends JsonResource
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
            'full_name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'verified' => $this->isVerified(),        
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),        
            'real_estate_count' => $this->realEstate->count(),        
            'units_count' => $this->unitReal->count(),        
            'completed_orders_count' => $this->contracts->where('is_completed', 1)->count(),        
            'uncompleted_orders_count' => $this->contracts->where('is_completed', 0)->count(),        
            // 'total_payment' => $this->payments->sum('amount'),        
            'photo_path' => $this->photo_path ? url("storage/{$this->photo_path}") : null,
        ];
    }
}
