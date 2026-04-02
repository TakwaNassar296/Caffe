<?php

namespace App\Http\Resources\Api;

use App\Http\Resources\Api\Driver\profileDriverResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $finance = $this->calculateFinance();
        return [
            'id'          => $this->id ?? null,
            'order_num'   => $this->order_num ?? null,
            'user_id'     => $this->user_id ?? null,
            'user_name'   => $this->user->full_name ?? null,
            'user'       => $this->user ? new ProfileResource($this->user) : null,
            'user_location' => $this->user_location_id ? new UserLocationResource($this->userLocation) : null,
            'status'      => $this->status ?? null,
            'created_at'  => $this->created_at->format('Y-m-d H:i:s') ?? null,
            'items'       => OrderItemResource::collection($this->items),
            'count_items' => $this->items->count(),
            'sub_total'   => $this->sub_total ?? null,
            'total_price' => $this->total_price ?? null,
            'driver_finance' =>  $this->driver_finance ??  0,
            'points_increase_user' =>  $this->points_increase_user ?? 0,
            'order_receipt_time' => $this->branch?->order_receipt_time ?? 10 ,
            'branch'      =>  $this->branch ? new BranchResource($this->branch) : null,
            'driver'      => $this->driver ? new profileDriverResource($this->driver) : null,
            'type'=>$this->type,
        ];
    }
}
