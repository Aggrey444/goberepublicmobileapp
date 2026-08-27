<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->string('q') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $categories = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 20));

        return ApiResponse::success('Categories retrieved successfully.', [
            'items' => CategoryResource::collection($categories->items()),
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
                'last_page' => $categories->lastPage(),
            ],
        ]);
    }

    public function show(Category $category): JsonResponse
    {
        return ApiResponse::success('Category retrieved successfully.', new CategoryResource($category));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $imagePath = $this->storeImage($request);

        $category = Category::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'status' => $validated['status'] ?? Category::STATUS_ACTIVE,
        ]);

        return ApiResponse::success('Category created successfully.', new CategoryResource($category), 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $this->deleteImage($category);
            $validated['image'] = $this->storeImage($request);
        }

        $category->update($validated);

        return ApiResponse::success('Category updated successfully.', new CategoryResource($category));
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->deleteImage($category);
        $category->delete();

        return ApiResponse::success('Category deleted successfully.');
    }

    private function storeImage(Request $request): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('categories', 'public');
    }

    private function deleteImage(Category $category): void
    {
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
    }
}
