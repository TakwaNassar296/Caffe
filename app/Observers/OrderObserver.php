<?php

namespace App\Observers;

use App\Models\Admin;
use App\Models\BranchMaterial;
use App\Models\BranchMaterialHistory;
use App\Models\BranchProduct;
use App\Models\Order;
use App\Notifications\NotificationAdmin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        $this->generateOrderNumber($order);
        $this->notifyAdmins($order);
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('status') && $order->status === 'completed') {
            $this->notifyAdmins($order);
            $this->updateProductsAmount($order);
            
            // Record material consumption when order is completed
            $materials = $this->getOrderMaterials($order);
            $this->recordMaterialConsumption($order, $materials);
            
            // Check and update product availability when materials reach zero
            $this->checkAndUpdateProductAvailability($order);
        }

        // Update branch materials quantities (for any status change)
        $materials = $this->getOrderMaterials($order);
        $this->updateBranchMaterials($order, $materials);
    }

    /**
     * Generate order number.
     */
    protected function generateOrderNumber(Order $order): void
    {
        $date = $order->created_at?->format('d.m.Y') ?? now()->format('d.m.Y');
        $branch = $order->branch;
        $governorateCode = $branch?->governorate?->code ?? '00';
        $cityCode = $branch?->city?->code ?? '00';
        $branchNumber = $branch?->code ?? '000';

        $typeShort = match (strtolower($order->type)) {
            'delivery' => 'D',
            'pick_up' => 'P',
            default => 'O',
        };

        $order->order_num = "{$date}.{$governorateCode}.{$cityCode}.{$branchNumber}{$typeShort}";
        $order->saveQuietly();
    }

    /**
     * Notify branch and super admins about order.
     */
    protected function notifyAdmins(Order $order): void
    {
        $admins = collect();

        // Branch admins
        if ($order->branch_id) {
            $branchAdmins = Admin::where('branch_id', $order->branch_id)->get();
            $branchAdmin = $order->branch?->admin;
            if ($branchAdmin && !$branchAdmins->contains($branchAdmin)) {
                $branchAdmins->push($branchAdmin);
            }
            $admins = $admins->merge($branchAdmins);
        }

        // Super admins
        $superAdmins = Admin::where('super_admin', 1)->get();
        $admins = $admins->merge($superAdmins);

        if ($admins->isNotEmpty()) {
            Notification::send($admins->unique('id'), new NotificationAdmin($order));
        }
    }

    /**
     * Get all materials from order products.
     */
    protected function getOrderMaterials(Order $order)
    {
        $orderItems = $order->items()->with('product', 'optionValues')->get();
        $allMaterials = collect();

        foreach ($orderItems as $orderItem) {
            $product = $orderItem->product;
            if (!$product) continue;

            $productsMaterials = $product->productsMaterials()->with(['items.material', 'productOption'])->get();

            foreach ($productsMaterials as $productMaterial) {
                foreach ($productMaterial->items as $item) {
                    if (!$item->material) continue;

                    $totalQuantityUsed = (float) $item->quantity_used * (float) $orderItem->quantity;

                    $allMaterials->push([
                        'material_id' => $item->material->id,
                        'material' => $item->material,
                        'quantity_used' => (float) $item->quantity_used,
                        'total_quantity_used' => $totalQuantityUsed,
                        'unit' => $item->unit,
                        'product_id' => $product->id,
                        'product' => $product,
                        'order_item' => $orderItem,
                        'product_material' => $productMaterial,
                        'product_material_item' => $item,
                    ]);
                }
            }
        }

        return $allMaterials;
    }

    /**
     * Update branch materials quantities.
     */
    protected function updateBranchMaterials(Order $order, $materials): void
    {
        if (!$order->branch_id || $materials->isEmpty()) return;

        $materials->groupBy('material_id')->each(function ($materialItems, $materialId) use ($order) {
            $firstItem = $materialItems->first();
            $branchMaterial = BranchMaterial::where('branch_id', $order->branch_id)
                ->where('material_id', $materialId)
                ->first();

            if (!$branchMaterial) return;

            $quantityToDeduct = $this->convertUnit(
                $materialItems->sum('total_quantity_used'),
                $firstItem['unit'],
                $branchMaterial->unit
            );

            $branchMaterial->updateQuietly([
                'current_quantity' => max(0, ($branchMaterial->current_quantity ?? 0) - $quantityToDeduct),
            ]);
        });
    }

    /**
     * Update products quantity in branch.
     */
    protected function updateProductsAmount(Order $order): void
    {
        if (!$order->branch_id) return;

        $order->items()->with('product')->get()->each(function ($orderItem) use ($order) {
            if (!$orderItem->product_id) return;

            $branchProduct = BranchProduct::where('branch_id', $order->branch_id)
                ->where('product_id', $orderItem->product_id)
                ->first();

            if (!$branchProduct) return;

            $branchProduct->updateQuietly([
                'amount' => max(0, ($branchProduct->amount ?? 0) - (float) $orderItem->quantity),
            ]);
        });
    }

    /**
     * Record material consumption when order is completed.
     * Groups consumption by material_id and consumed_date - shows total only, not per product.
     */
    protected function recordMaterialConsumption(Order $order, $materials): void
    {
        if (!$order->branch_id || $materials->isEmpty()) return;

        $today = Carbon::today();
        $branch = $order->branch;

        DB::transaction(function () use ($order, $materials, $today, $branch) {
            $materials->groupBy('material_id')->each(function ($materialItems, $materialId) use ($order, $today, $branch) {
                $firstItem = $materialItems->first();
                $branchMaterial = BranchMaterial::where('branch_id', $order->branch_id)
                    ->where('material_id', $materialId)
                    ->first();

                if (!$branchMaterial) return;

                // Calculate total quantity used for this material (sum of all products)
                $totalQuantityUsed = $materialItems->sum('total_quantity_used');
                $quantityToRecord = $this->convertUnit(
                    $totalQuantityUsed,
                    $firstItem['unit'],
                    $branchMaterial->unit
                );

                // Get sent_date from the most recent shipment (sent status) for this material
                $sentDate = BranchMaterialHistory::where('branch_material_id', $branchMaterial->id)
                    ->where('status', 'sent')
                    ->orderBy('transaction_date', 'desc')
                    ->first()?->transaction_date ?? $today;

                // Check if consumption record already exists for this material and date
                $existingConsumption = \App\Models\BranchMaterialHistory::where('branch_id', $order->branch_id)
                    ->where('material_id', $materialId)
                    ->where('status', 'consumed')
                    ->where('transaction_date', $today)
                    ->first();

                if ($existingConsumption) {
                    // Update existing record - add to previous total
                    $existingConsumption->update([
                        'quantity' => $existingConsumption->quantity + $quantityToRecord,
                        'order_id' => $order->id, // Update to latest order
                    ]);
                } else {
                    // Create new consumption record with total quantity
                    BranchMaterialHistory::create([
                        'branch_id' => $order->branch_id,
                        'branch_material_id' => $branchMaterial->id,
                        'material_id' => $materialId,
                        'order_id' => $order->id,
                        'quantity' => $quantityToRecord,
                        'unit' => $branchMaterial->unit,
                        'status' => 'consumed',
                        'transaction_date' => $today,
                        'sent_date' => $sentDate,
                        'consumer_type' => 'branch',
                        'consumer_name' => $branch->name ?? 'Branch',
                        'notes' => null,
                    ]);
                }
            });
        });
    }

    /**
     * Check if all materials for a product are zero and update product status to unavailable.
     */
    protected function checkAndUpdateProductAvailability(Order $order): void
    {
        if (!$order->branch_id) return;

        $order->items()->with('product')->get()->each(function ($orderItem) use ($order) {
            if (!$orderItem->product_id) return;

            $product = $orderItem->product;
            if (!$product) return;

            // Get all materials required for this product
            $productsMaterials = $product->productsMaterials()->with(['items.material'])->get();
            
            if ($productsMaterials->isEmpty()) return;

            // Check if all materials for this product have zero quantity in branch
            $allMaterialsZero = true;
            foreach ($productsMaterials as $productMaterial) {
                foreach ($productMaterial->items as $item) {
                    if (!$item->material) continue;

                    $branchMaterial = BranchMaterial::where('branch_id', $order->branch_id)
                        ->where('material_id', $item->material->id)
                        ->first();

                    if ($branchMaterial && ($branchMaterial->current_quantity ?? 0) > 0) {
                        $allMaterialsZero = false;
                        break 2;
                    }
                }
            }

            // If all materials are zero, set product status to unavailable
            if ($allMaterialsZero) {
                $branchProduct = BranchProduct::where('branch_id', $order->branch_id)
                    ->where('product_id', $product->id)
                    ->first();

                if ($branchProduct) {
                    $branchProduct->updateQuietly([
                        'status' => 0, // Set to unavailable
                    ]);
                }
            }
        });
    }

    /**
     * Convert quantity between units.
     */
    protected function convertUnit(float $quantity, string $from, string $to): float
    {
        if ($from === $to) return $quantity;

        $conversionRates = [
            'kg' => ['g' => 1000],
            'g'  => ['kg' => 0.001],
            'l'  => ['ml' => 1000],
            'ml' => ['l'  => 0.001],
        ];

        return ($conversionRates[$from][$to] ?? 1) * $quantity;
    }
}
