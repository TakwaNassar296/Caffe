<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;

class BranchCategoryController extends Controller
{
    use ApiResponse;
        public function index($branchId)
    {
       $branch = Branch::find($branchId);

        if (!$branch){
            return $this->errorResponse("Branch Not Found" , 404);
        }

        $products = $branch->products()->where('is_active',1)->with('category')->get();

        $categoryIds = $products->pluck('category.id')->unique();

        $categories = Category::whereIn('id', $categoryIds)->get();

        if ($categories->isEmpty()) {
            return $this->successResponse('No categories found for this branch', []);
        }

        return $this->successResponse('Categories retrieved successfully', CategoryResource::collection($categories));
    }
}
