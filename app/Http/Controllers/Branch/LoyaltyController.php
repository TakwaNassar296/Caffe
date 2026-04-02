<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\StoreAwardPointsRequest;
use App\Http\Resources\Branch\AdminResource;
use App\Http\Resources\Branch\AwardPointsResource;
use App\Models\Admin;
use App\Models\EmployeePoint;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class LoyaltyController extends Controller
{
    Use ApiResponse;

    public function employees()
    {
        $employees = Admin::where('role', 'employee')
            ->withSum('points', 'point_amount')
            ->paginate(10);

        return $this->PaginationResponse(
            AdminResource::collection($employees),
            'Employees retrieved successfully.',
            200
        );
    }


    public function awardPoints(StoreAwardPointsRequest $request)
    {
        $employee = Admin::where('id', $request->employee_id)
            ->where('role', 'employee')
            ->first();
 
        if (!$employee) {
            return $this->errorResponse('Employee not found or invalid role.', 404);
        }

        try {
            DB::beginTransaction();

            // Create employee point record
            $employeePoint = EmployeePoint::create([
                'employee_id' => $request->employee_id,
                'point_amount' => $request->point_amount,
                'type_reason' => $request->type_reason,
                'other_reason' => $request->type_reason === 'other' ? $request->other_reason : null,
                'notes' => $request->notes ? ['notes' => $request->notes] : null,
            ]);

            // Update employee's total points (add new points to existing total)
            $employee->increment('total_points', $request->point_amount);

            DB::commit();

            // Load employee relationship for the resource
            $employeePoint->load('employee');

            return $this->successResponse(
                'Points awarded successfully.',
                new AwardPointsResource($employeePoint),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to award points: ' . $e->getMessage(), 500);
        }
    }
}
