<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\DeliveryInformation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['items', 'deliveryInformation', 'payment'])
            ->orderByDesc('created_at')
            ->get();

        return ApiResponse::success('Orders retrieved successfully.', OrderResource::collection($orders));
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorizeOrder($request, $order);
        $order->load(['items', 'deliveryInformation', 'payment']);

        return ApiResponse::success('Order retrieved successfully.', new OrderResource($order));
    }

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->with('items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return ApiResponse::error('Your cart is empty.', null, 422);
        }

        foreach ($cart->items as $item) {
            if (!$item->product || $item->product->status !== 'active') {
                return ApiResponse::error('One of your cart items is no longer available.', null, 422);
            }
        }

        $validated = $request->validated();

        $delivery = $validated['delivery_information'] ?? $validated;

        $subtotal = $cart->subtotal();
        $deliveryFee = 0; // TBD: delivery fee calculation per business rules
        $total = $subtotal + $deliveryFee;

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => self::generateOrderNumber(),
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'payment_status' => Order::PAYMENT_STATUS_PENDING,
            'order_status' => Order::ORDER_STATUS_PENDING,
        ]);

        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->product->price,
                'total' => (float) $item->product->price * $item->quantity,
            ]);
        }

        DeliveryInformation::create([
            'order_id' => $order->id,
            'recipient_name' => $delivery['recipient_name'],
            'phone' => $delivery['phone'],
            'address' => $delivery['address'],
            'city' => $delivery['city'] ?? null,
            'additional_notes' => $delivery['additional_notes'] ?? null,
        ]);

        $order->update(['order_status' => Order::ORDER_STATUS_PAYMENT_PENDING]);

        $cart->items()->delete();
        $order->load(['items', 'deliveryInformation']);

        app(\App\Services\NotificationService::class)->notify(
            $user->id,
            'Order placed',
            "Your order {$order->order_number} was received successfully.",
            Order::class,
            ['type' => 'order_created', 'order_id' => $order->id, 'order_number' => $order->order_number],
        );

        return ApiResponse::success('Order created successfully.', new OrderResource($order), 201);
    }

    public static function generateOrderNumber(): string
    {
        do {
            $number = 'GOBE-' . strtoupper(Str::random(8));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        abort_if($order->user_id !== $request->user()->id, 403, 'You do not have access to this order.');
    }
}
