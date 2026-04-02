<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * Get all categories
     * Optionally filter by categories that have products in the branch
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->branch_id) {
            return $this->errorResponse('Branch not found for this user.', 404);
        }

        $branchId = $user->branch_id;
        $onlyWithProducts = $request->input('only_with_products', false);

        $query = Category::query()
            ->where('is_active', 1);

        // If only_with_products is true, filter categories that have products in this branch
        if ($onlyWithProducts) {
            $query->whereHas('products', function ($q) use ($branchId) {
                $q->whereHas('branches', function ($query) use ($branchId) {
                    $query->where('branches.id', $branchId)
                        ->where('branch_product.status', 1);
                });
            });
        }

        $categories = $query->get();

        if ($categories->isEmpty()) {
            return $this->successResponse(
                'No categories found.',
                [],
                200
            );
        }

        return $this->successResponse(
            'Categories retrieved successfully.',
            CategoryResource::collection($categories),
            200
        );
    }

    /**
     * Get a single category by ID
     */
    public function show($id)
    {
        $category = Category::where('is_active', 1)->find($id);

        if (!$category) {
            return $this->errorResponse('Category not found.', 404);
        }

        return $this->successResponse(
            'Category retrieved successfully.',
            new CategoryResource($category),
            200
        );
    }
}
