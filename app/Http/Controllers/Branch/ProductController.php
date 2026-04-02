<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Resources\Branch\ProductBranchResource;
use App\Http\Resources\Branch\SingleProductBranchResource;
use App\Http\Resources\ProductResource;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Category;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    public function products(Request $request)
{
    $user = $request->user();

    $branchId = Branch::where('admin_id', $user->id)->value('id');

    if (!$branchId) {
        return $this->errorResponse('Branch not found for this user.', 404);
    }

    $categoryId   = $request->input('category_id');
    $is_available = $request->input('is_available');

    $branchProducts = BranchProduct::query()
        ->where('branch_id', $branchId)
        ->where('status', 1)
        ->when($is_available !== null, function ($query) use ($is_available) {
            // Handles "true", "1", "false", "0" correctly
            $isAvailable = in_array(strtolower((string) $is_available), ['true', '1', 'yes']);
            $query->when($isAvailable,
                fn($q) => $q->where('amount', '>', 0),
                fn($q) => $q->where('amount', '<=', 0)
            );
        })
        ->whereHas('product', function ($q) use ($categoryId) {
            $q->where('is_active', true);
            if ($categoryId) {
                $q->where('category_id', $categoryId);
            }
        })
        ->with('product')
        ->paginate(10);

    $products = $branchProducts->getCollection()
        ->map(function ($branchProduct) {
            if ($branchProduct->product) {
                $branchProduct->product->setRelation('branchProduct', $branchProduct);
                return $branchProduct->product;
            }
            return null;
        })
        ->filter()
        ->values();

    $branchProducts->setCollection($products);

    return $this->PaginationResponse(
        ProductBranchResource::collection($branchProducts),
        'Products retrieved successfully.',
        200
    );
}

  public function totalCategories(Request $request)
    {
        $branchId = $request->user()->branch_id;

        $categories = Category::query()
            ->withCount([
                'products as count' => function ($q) use ($branchId) {
                    $q->whereHas('branches', function ($query) use ($branchId) {
                        $query->where('branches.id', $branchId);
                    });
                }
            ])
            ->get(['id', 'name']);

        return response()->json([
                'data' => $categories->map(fn ($c) => [
                'id'    => $c->id,
                'name'  => $c->name,
                'count' => (int) $c->count,
            ]),
        ], 200);
    }

     public function toggleActive(Product $product)
    {
         $product->update([
            'is_active' => ! $product->is_active,
        ]);

        return $this->successResponse(
            'Product status updated successfully',
            [
                'id'     => $product->id,
                'name'   => $product->name,
                'status' => (int) $product->is_active,
            ]
        );
    }


   public function show(Request $request, $id)
{
    $user = $request->user();

   
    

     $branchId = Branch::where('admin_id', $user->id)->value('id');

    $branchProduct = BranchProduct::where('branch_id', $branchId)
        ->where('product_id', $id)
        ->where('status', 1)
        ->with(['product.options', 'product.category'])
        ->first();

    if (!$branchProduct || !$branchProduct->product) {
        return $this->errorResponse('Product not found in this branch.', 404);
    }

    $product = $branchProduct->product;
    $product->setRelation('branchProduct', $branchProduct);

    return $this->successResponse(
        'Product retrieved successfully.',
        new SingleProductBranchResource($product),
        200
    );
}

    



}
