<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::where('status', Category::STATUS_ACTIVE)->get();

        return ApiResponse::success('Categories retrieved successfully.', CategoryResource::collection($categories));
    }

    public function show(Category $category): JsonResponse
    {
        if ($category->status !== Category::STATUS_ACTIVE) {
            return ApiResponse::error('Category not found.', null, 404);
        }

        return ApiResponse::success('Category retrieved successfully.', new CategoryResource($category));
    }
}
