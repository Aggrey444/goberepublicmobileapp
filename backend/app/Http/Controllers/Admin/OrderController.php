<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()->with(['user', 'items', 'deliveryInformation', 'payment']);

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->string('order_status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->string('payment_status'));
        }

        if ($request->filled('q')) {
            $search = $request->string('q');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%');
            });
        }

        $orders = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 20));

        return ApiResponse::success('Orders retrieved successfully.', [
            'items' => OrderResource::collection($orders->items()),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['user', 'items', 'deliveryInformation', 'payment']);

        return ApiResponse::success('Order retrieved successfully.', new OrderResource($order));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $target = $request->validated()['order_status'];

        if (!$order->canTransitTo($target)) {
            return ApiResponse::error(
                "Cannot transition order from {$order->order_status} to {$target}.",
                null,
                422
            );
        }

        $order->update(['order_status' => $target]);
        $order->load(['user', 'items', 'deliveryInformation', 'payment']);

        $statusLabel = str_replace('_', ' ', strtolower($target));
        app(\App\Services\NotificationService::class)->notify(
            $order->user_id,
            'Order status updated',
            "Your order {$order->order_number} is now {$statusLabel}.",
            Order::class,
            ['type' => 'order_status', 'order_id' => $order->id, 'order_number' => $order->order_number, 'status' => $target],
        );

        return ApiResponse::success('Order status updated successfully.', new OrderResource($order));
    }
}
