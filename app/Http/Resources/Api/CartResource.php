<?php

namespace App\Http\Resources\Api;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use App\Http\Resources\Api\CartItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sub_total = $this->items->sum('total_price');
        $free_delivery_minimum = (float) SiteSetting::value('free_delivery_minimum', 0);
        if ($sub_total >= $free_delivery_minimum) {
            $delivery_charge = 0;
        } else {
            $delivery_charge = (float) SiteSetting::value('delivery_charge', 0);
        }
        $tax_percentage = (float) SiteSetting::value('tax_percentage', 0);
        $tax = round(($sub_total * $tax_percentage) / 100, 2);
        $total = $sub_total + $delivery_charge + $tax;


            $descriptionDelivery =  SiteSetting::value('text_cart' ,"Discount applies to selected items ordered with your added product");


        
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'is_delivery' =>  $this->branch->is_delivery ?? 1,
            'items' => CartItemResource::collection($this->items),
            'sub_total' => $sub_total,
            'delivery_charge' => $delivery_charge,
            'tax' => $tax,
            'total' => $total,
            'items_count' => $this->items->count(),
            'description_red_text' => $descriptionDelivery,

        ];
    }
}
