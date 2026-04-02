<?php

namespace App\Http\Resources\Admin\V2\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'base_salary' => $this->base_salary,
            'role' => $this->role,
            
    
       
                'is_active' => (bool) $this->is_active,
                'is_online' => (bool) $this->is_online,
                'is_blocked' => $this->blocked_until ? now()->lessThan($this->blocked_until) : false,
                'blocked_until' => $this->blocked_until,
                'reason_of_block' => $this->reason_of_block,
           
            
          
                'profile_image' => $this->profile_image ? url($this->profile_image) : null,
                'facebook' => $this->facebook,
                'instagram' => $this->instagram,
                'whatsapp' => $this->whatsapp,
                'snapchat' => $this->snapchat,
                'tiktok' => $this->tiktok,
                'twitter' => $this->twitter,
    
                'salaries_count' => $this->salaries?->count() ?? 0,
                'notes_count' => $this->notes?->count() ?? 0,
                'received_contracts_count' => $this->receivedContract?->count() ?? 0,
                'refundable_contracts_count' => $this->refundableContract?->count() ?? 0,
         
          
          
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
