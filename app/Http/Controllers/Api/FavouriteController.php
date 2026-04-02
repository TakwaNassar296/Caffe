<?php

namespace App\Http\Controllers\Api;


use App\Models\Favourite;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\FavouriteResource;
use Illuminate\Support\Facades\Validator;

class FavouriteController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $favorites = Favourite::with(['product'])
            ->where('user_id', auth('user')->id())
            ->get();

        return $this->successResponse(
            'Favourites retrieved successfully',
            FavouriteResource::collection($favorites)
        );
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        if ($validator->fails()) {

            return $this->errorResponse($validator->errors()->first());
        }

        $userId = auth('user')->id();
        $productId = $request->product_id;
        $branchId = $request->branch_id;
        $exists = Favourite::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->exists();

        if ($exists) {
            return $this->errorResponse('Product already in favorites');
        }

        $favorites = Favourite::create([
            'user_id' => $userId,
            'product_id' => $productId,
            'branch_id' => $branchId,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Product added to favorites',
            'data' => new FavouriteResource($favorites),
        ], 201);
    }
    public function destroy($id)
    {
        $favourite = Favourite::where('id', $id)
            ->where('user_id', auth('user')->id())
            ->first();

        if (!$favourite) {
            return $this->errorResponse(' Favourite not foundd', 403);
        }


        $favourite->delete();

        return $this->successResponse('Product removed from favorites');
    }

    public function clearAll()
    {
        $deleted = Favourite::where('user_id', auth('user')->id())->delete();

        if ($deleted) {
            return $this->successResponse('All favorites cleared successfully');
        }

        return $this->errorResponse('No favorites found to clear', 404);
    }
}
