<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class BranchUserResource extends JsonResource
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
            'description' => $this->description,
            'code' => $this->code,
            'image' => $this->image ?  asset('storage/' . $this->image) : asset('images/coffee.png'),
            'opening_date' => $this->opening_date,
            'close_date' => $this->close_date,
            'scope_work' => $this->scope_work,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'phone_number' => $this->phone_number,
            'is_delivery' =>  $this->is_delivery ?? 1,
            'is_follow' => auth()->check() && DB::table('branch_user')
                ->where('branch_id', $this->id)
                ->where('user_id', auth()->id())
                ->exists(),

             'total_rating'   => $this->reviews()->exists()
                ? $this->reviews()->sum('rating')
                : 0,

            'average_rating' => $this->reviews()->exists()
                ? round($this->reviews()->avg('rating'), 1)
                : 0,

            'total_orders'   => $this->orders()->exists()
                ? $this->orders()->count()
                : 0,

            'followed' => auth('user')->check()
                ? $this->users()->where('user_id', auth('user')->id())->exists()
                : false,
        ];
    }
}
