<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::where('role', User::ROLE_CUSTOMER)->withCount('orders');

        if ($request->filled('q')) {
            $search = $request->string('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $customers = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 20));

        return ApiResponse::success('Customers retrieved successfully.', [
            'items' => UserResource::collection($customers->items()),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
            ],
        ]);
    }

    public function show(User $user): JsonResponse
    {
        if (!$user->isCustomer()) {
            return ApiResponse::error('Not a customer account.', null, 404);
        }

        $user->load(['orders.items', 'orders.payment']);

        return ApiResponse::success('Customer retrieved successfully.', new UserResource($user));
    }
}
