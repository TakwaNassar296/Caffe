<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductDetailsResourse;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Branch;
use App\Traits\ApiResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $data = Product::paginate(10);

        if ($data->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'There are no products at the moment',
                'data' => [],
            ], Response::HTTP_OK);
        }

        return response()->json([
            'status' => true,
            'message' => 'Product List',
            'data' => ProductResource::collection($data),
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ],
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
        $product = Product::where('id', $id)->first();
        if (! $product) {
            return response()->json([
                'status' => false,
                'message' => 'Product Not Found',
                'data' => [],
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => true,
            'message' => 'Product Item',
            'data' => new ProductDetailsResourse($product),
        ], Response::HTTP_OK);
    }

    public function appereInCart()
    {
        $user = Auth::guard('user')->user();
        
        // إذا المستخدم غير موجود، يرجع خطأ
        if (!$user) {
            return $this->errorResponse('User not authenticated', 401);
        }

        if (!$user->cart || !$user->cart->branch_id) {
            return $this->errorResponse('Cart is empty', 404);
        }

        $branch_id = $user->cart->branch_id;

        $branch = Branch::find($branch_id);
        
        if (!$branch) {
            return $this->errorResponse('Branch not found', 404);
        }

        $data = $branch->products()
            ->where('appere_in_cart', 1)
            ->where('is_active', 1)
            ->paginate(10);

        if ($data->isEmpty()) {
            return $this->errorResponse('There are no products at the moment', 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Product List',
            'data' => ['products' => ProductResource::collection($data), 'branch_id' => $branch_id],
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ],
        ], Response::HTTP_OK);
    }

    public function relatedProducts()
    {
        $user = Auth::guard('user')->user();
        $user = auth('user')->user();

        if (! $user || ! $user->cart) {
            return response()->json([
                'status' => false,
                'message' => 'User has no cart',
                'data' => ['related_products' => []],
            ]);
        }

        $cartItems = $user->cart->items()->with('product.relatedProducts')->get();

        $relatedProducts = $cartItems->flatMap(function ($item) {
            return $item->product?->relatedProducts ?? collect();
        })->unique('id')->values();


      $data = $relatedProducts->map(function ($product) {
         return [
        'id' => $product->id,
        'name' => $product->name,
        'images' => is_array($product->image)
            ? array_map(fn($img) => url("storage/{$img}"), $product->image)
            : [],
        'title' => $product->title,
        'description' => $product->description,
        'price' => (int) $product->price,
        'points' => $product->points,
        'stars' => $product->stars,
        'rating_avg' => $product->averageRating(),
        'reviews_count' => $product->reviewsCount(),
        'delivery_time' => $product->delivery_time,
        'total_sales' => $product->total_sales,
        'remaining_quantity' => $product->remaining_quantity,
    ];
});
        return $this->successResponse('Cart items with related products', [
            'related_products' => $data,
        ]);
    }
}
