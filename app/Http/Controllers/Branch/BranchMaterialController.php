<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\ConfirmDeliveryRequest;
use App\Http\Resources\Branch\DeliveryConfirmationResource;
use App\Http\Resources\Branch\MaterialResource;
use App\Models\Branch;
use App\Models\BranchMaterial;
use App\Models\Material;
use App\Models\RequestMaterial;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BranchMaterialController extends Controller
{
    use ApiResponse;

    /**
     * Get internal materials for the authenticated branch
     * Returns only materials where material_type = 'internal'
     */
    public function materials()
    {
        $user = Auth::user();

        if (!$user || !$user->branch_id) {
            return $this->errorResponse('Branch not found for this user.', 404);
        }

       $branchId = Branch::where('admin_id', $user->id)->value('id');

        // Get branch materials where the related material has material_type = 'internal'
        $materials = BranchMaterial::where('branch_id', $branchId)
            ->whereHas('material', function ($query) {
                $query->where('material_type', 'internal');
            })
            ->with('material.category')
            ->get();

        return $this->successResponse(
            'Internal materials retrieved successfully.',
            MaterialResource::collection($materials),
            200
        );
    }

    /**
     * Create a material request
     */
    public function createMaterialRequest(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->branch_id) {
            return $this->errorResponse('Branch not found for this user.', 404);
        }

        $validator = Validator::make($request->all(), [
            'material_id' => 'required|exists:materials,id',
            'quantity' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $branchId = $user->branch_id;
        $material = Material::find($request->material_id);
        
        // Validate that material is internal type
        if (!$material || $material->material_type !== 'internal') {
            return $this->errorResponse(
                'Only internal materials can be requested through this endpoint.',
                422
            );
        }

        $branchMaterial = BranchMaterial::where('branch_id', $branchId)
            ->where('material_id', $request->material_id)
            ->first();

        // Validate quantity against max limit
        if ($branchMaterial && $branchMaterial->max_limit) {
            $currentStock = $branchMaterial->quantity_in_stock ?? 0;
            $requestedQty = (float) $request->quantity;
            
            if (($currentStock + $requestedQty) > $branchMaterial->max_limit) {
                return $this->errorResponse(
                    "Requested quantity exceeds maximum limit. Maximum allowed: {$branchMaterial->max_limit}",
                    422
                );
            }
        }

        // Validate against central material stock
        if ($material && $material->quantity_in_stock) {
            $requestedQty = (float) $request->quantity;
            if ($requestedQty > $material->quantity_in_stock) {
                return $this->errorResponse(
                    "Requested quantity exceeds available stock. Available: {$material->quantity_in_stock} {$material->unit}",
                    422
                );
            }
        }

        DB::beginTransaction();
        try {
            $materialRequest = RequestMaterial::create([
                'branch_id' => $branchId,
                'material_id' => $request->material_id,
                'quantity' => $request->quantity,
                'status' => 'pending',
                'comment' => $request->comment,
            ]);

            DB::commit();

            return $this->successResponse(
                'Material request created successfully.',
                [
                    'request_id' => $materialRequest->id,
                    'status' => $materialRequest->status,
                    'type' => $materialRequest->material->material_type ?? 'internal',
                    'material' => $materialRequest->material->name ?? null,
                    'quantity' => $materialRequest->quantity,
                ],
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create material request', 500);
        }
    }

    /**
     * Get material requests for the branch
     */
  public function getMaterialRequests(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->branch_id) {
            return $this->errorResponse('Branch not found for this user.', 404);
        }

        $branchId = $user->branch_id;
        $status = $request->input('status');
        $type = $request->input('type'); // Filter by material type

        $requests = RequestMaterial::where('branch_id', $branchId)
            ->with(['material', 'latestApproval'])
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($type, function ($q) use ($type) {
                $q->whereHas('material', function ($query) use ($type) {
                    $query->where('material_type', $type);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $requests->map(function ($request) {
            return [
                'request_id' => $request->id,
                'quantity' => (float) $request->quantity,
                'name' => $request->material?->name,
                'type' => $request->material?->material_type ?? 'internal',
                'date' => optional($request->updated_at)->toDateTimeString(),
                'status' => $request->latestApproval->action ?? 'pending',
                'admin_response' => $request->latestApproval?->comment ?? 'under review',
            ];
        });

        return $this->successResponse(
            'Material requests retrieved successfully',
            $data
        );
    }

    /**
     * Get approval history for all material requests
     */
 
public function getApprovalHistory(Request $request)
{
    $user = Auth::user();

    if (!$user || !$user->branch_id) {
        return $this->errorResponse('Branch not found for this user.', 404);
    }

        $type = $request->input('type'); // Filter by material type

        $requests = RequestMaterial::where('branch_id', $user->branch_id)
            ->with(['material', 'latestApproval.admin'])
            ->when($type, function ($q) use ($type) {
                $q->whereHas('material', function ($query) use ($type) {
                    $query->where('material_type', $type);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $requests->map(function ($request) {
            return [
                'id' => $request->id,
                'item_name' => $request->material?->name ?? 'Unknown',
                'quantity' => (float) $request->quantity,
                'type' => $request->material?->material_type ?? 'internal',
                'date' => optional($request->updated_at)->toDateTimeString(),
                'status' => $request->latestApproval->action ?? 'pending',
                'performed_by' => $request->latestApproval?->admin?->name ?? 'N/A',
                'notes' => $request->latestApproval?->comment ?? 'No notes',
            ];
        });

    return $this->successResponse(
        'Material requests status retrieved successfully',
        $data
    );
}

    /**
     * Confirm delivery/receipt of material request
     */
    public function confirmDelivery(ConfirmDeliveryRequest $request, $requestId)
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return $this->errorResponse('Branch not found for this user.', 404);
        }

        $materialRequest = $this->findMaterialRequest($requestId, $user->branch_id);
        if (!$materialRequest) {
            return $this->errorResponse('Material request not found.', 404);
        }
 
        return $this->updateDeliveryStatus($materialRequest, $request);
    }

    /**
     * Get authenticated user with branch validation
     */
    private function getAuthenticatedUser()
    {
        $user = Auth::user();
        return ($user && $user->branch_id) ? $user : null;
    }


    /**
     * Find material request by ID and branch ID
     */
    private function findMaterialRequest($requestId, $branchId)
    {
        return RequestMaterial::where('branch_id', $branchId)
            ->find($requestId);
    }
 

    /**
     * Update delivery status in database
     */
    private function updateDeliveryStatus(RequestMaterial $materialRequest, ConfirmDeliveryRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $materialRequest->update([
                'delivery_status' => $request->delivery_status,
                'delivery_feedback' => $request->delivery_feedback,
                'delivery_confirmed_at' => now(),
            ]);

            DB::commit();

            return $this->buildSuccessResponse($materialRequest);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to confirm delivery', 500);
        }
    }

    /**
     * Build success response for delivery confirmation using resource
     */
    private function buildSuccessResponse(RequestMaterial $materialRequest)
    {
        return $this->successResponse(
            'Delivery confirmation updated successfully.',
            new DeliveryConfirmationResource($materialRequest),
            200
        );
    }
}
