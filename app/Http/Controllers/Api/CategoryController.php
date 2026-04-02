<?php
namespace App\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\CategoryWithProductResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function index()
    {
        $data = Category::all();  

        if ($data->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'There are no categories at the moment',
                'data' => [],
            ], Response::HTTP_OK);
        }

        return response()->json([
            'status' => true,
            'message' => 'Category List',
            'data' => CategoryResource::collection($data),
        ], Response::HTTP_OK);
    }

    public function categoryWithProduct()
    {
        $categories = Category::with('products')->get();

        return response()->json([
            'status' => true,
            'message' => 'Categories with Products',
            'data' => CategoryWithProductResource::collection($categories),
        ], Response::HTTP_OK);
    }
}
