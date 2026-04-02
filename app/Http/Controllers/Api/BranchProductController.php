<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\FilterBranchProductsRequest;
use App\Http\Resources\Api\BranchResource;
use App\Http\Resources\ProductResource;
use App\Models\Branch;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BranchProductController extends Controller
{
    use ApiResponse;

    public function baseProducts($branchId)
    {
        $branch = Branch::find($branchId);

        if (! $branch) {
            return $this->errorResponse('Branch Not Found', 404);
        }

        $products = $branch->products()
            ->where('is_active', 1)
            ->with(['category'])
            ->withAvg('reviews', 'rating')
            ->get();

        if ($products->isEmpty()) {
            return $this->successResponse('No products found', []);
        }

        // أقل وأعلى سعر
        $maxPrice = $products->max('price');
        $minPrice = $products->min('price');

        // جديد خلال 24 ساعة
        $newProducts = $products->filter(
            fn ($p) => $p->created_at >= now()->subDay()
        )->values();

        // الأكثر شعبية
        $popularProducts = $products->sortByDesc(
            fn ($p) => $p->reviews_avg_rating ?? 0
        )->values();

        // تصنيف المنتجات حسب الكاتيجوري
        $categoriesData = $products
            ->pluck('category')
            ->unique('id')
            ->values()
            ->map(function ($cat) use ($branchId) {
                $category = Category::with(['products' => function ($q) use ($branchId) {
                    $q->whereHas('branches', fn ($b) => $b->where('branches.id', $branchId));
                }])->find($cat->id);

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => $category->image ? url("storage/{$category->image}") : null,
                    'products' => ProductResource::collection($category->products),
                ];
            });

        return $this->successResponse('Products retrieved successfully', [
            'branch' => new BranchResource($branch),
            'new' => ProductResource::collection($newProducts),
            'popular' => ProductResource::collection($popularProducts),
            'categories' => $categoriesData,
            'max_price' => $maxPrice,
            'min_price' => $minPrice,
        ]);
    }

    public function filterProducts(Request $request, $branchId)
    {
        $branch = Branch::find($branchId);

        if (! $branch) {
            return $this->errorResponse('Branch Not Found', 404);
        }

        $filters = $request->only([
            'name', 'category_id', 'is_new', 'is_top_rated',
            'min_price', 'max_price', 'heights_points',
            'heights_stars', 'fast_delivery', 'highest_price', 'lowest_price'
        ]);

        $products = $branch->products()
            ->where('is_active', 1)
            ->withAvg('reviews', 'rating')
            ->with('category')
            ->filter($filters)
            ->paginate(25);

        if ($products->isEmpty()) {
            return $this->successResponse('No products match your filters', []);
        }

        $collection = ProductResource::collection($products)
            ->additional([
                'max_price' => $products->max('price'),
                'min_price' => $products->min('price'),
            ]);

        return $this->PaginationResponse($collection, 'Filtered products retrieved successfully');
    }

    public function priceRange($branchId)
    {
        $branch = Branch::find($branchId);

        if (! $branch) {
            return $this->errorResponse('Branch Not Found', 404);
        }

        $products = $branch->products()->get();

        $discountedPrices = $products->map(fn ($product) => $product->calcPrice());

        $minPrice = round($discountedPrices->min(), 1);
        $maxPrice = round($discountedPrices->max(), 1);

        return $this->successResponse('Price range retrieved successfully', [
            'min_price' => (float) $minPrice,
            'max_price' => (float) $maxPrice,
        ]);
    }
}
