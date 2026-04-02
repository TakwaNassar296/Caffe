<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;
use App\Traits\GeoTrait;
use App\Models\SiteSetting;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\BranchResource;
use App\Http\Resources\Api\BranchUserResource;
use App\Http\Requests\Api\BranchUserRequest\BranchUserRequest;

class BranchUserController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
      $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 402, []);
        }

        if (! $request->filled('latitude') || ! $request->filled('longitude')) {
            return $this->errorResponse('Please activate your location');
        }

        $maxRange = SiteSetting::value('max_range') ?? 5;
        $userLat = $request->latitude;
        $userLng = $request->longitude;

        $branches = Branch::query()
            ->when($request->filled('name'), function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->name}%");
            })
            ->get();

        $branches = $branches->map(function ($branch) use ($userLat, $userLng) {
            $branch->distance = GeoTrait::distanceKm(
                $userLat,
                $userLng,
                $branch->latitude,
                $branch->longitude
            );

            return $branch;
        });

        $branches = $branches
            // ->filter(fn ($branch) => $branch->distance <= $maxRange)
            ->sortBy('distance')
            ->values();

        if ($branches->isEmpty()) {
            return $this->successResponse("No branches found within {$maxRange} km");
        }

        return $this->successResponse(
            "Branches within {$maxRange} km",
            BranchUserResource::collection($branches)
        );
    }

    public function follow(BranchUserRequest $request)
    {
        $user = Auth::guard('user')->user();

        $user->branches()->syncWithoutDetaching($request['branch_id']);

        return $this->successResponse('Added Branche To User Successfully');
    }

    public function delete($branchId)
    {
        $user = Auth::guard('user')->user();

        $exists = $user->branches()->where('branch_id', $branchId)->exists();

        if (! $exists) {
            return $this->errorResponse('Branch is not assigned to this user');
        }

        $user->branches()->detach($branchId);

        return $this->successResponse('Branch removed from user successfully');
    }

    public function getBranches(Request $request)
    {
       
        $branches = Branch::all();
       return $this->successResponse('Branches fetched Successfully',BranchResource::collection(  $branches));
    }
}
