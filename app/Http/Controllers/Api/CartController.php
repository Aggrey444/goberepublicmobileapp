<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());
        $cart->load('items.product.category');

        return ApiResponse::success('Cart retrieved successfully.', new CartResource($cart));
    }

    public function add(AddToCartRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $product = Product::findOrFail($validated['product_id']);

        if ($product->status !== Product::STATUS_ACTIVE) {
            return ApiResponse::error('This product is not available.', null, 422);
        }

        $cart = $this->getOrCreateCart($request->user());

        $item = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);
        $item->quantity = ($item->quantity ?? 0) + $validated['quantity'];
        $item->unit_price = $product->price;
        $item->save();

        $cart->load('items.product');

        return ApiResponse::success('Item added to cart.', new CartResource($cart));
    }

    public function update(UpdateCartItemRequest $request, int $itemId): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());
        $item = $cart->items()->findOrFail($itemId);
        $item->update(['quantity' => $request->validated()['quantity']]);

        $cart->load('items.product');

        return ApiResponse::success('Cart updated.', new CartResource($cart));
    }

    public function remove(Request $request, int $itemId): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());
        $item = $cart->items()->findOrFail($itemId);
        $item->delete();

        $cart->load('items.product');

        return ApiResponse::success('Item removed from cart.', new CartResource($cart));
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());
        $cart->items()->delete();

        $cart->load('items.product');

        return ApiResponse::success('Cart cleared.', new CartResource($cart));
    }

    private function getOrCreateCart($user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }
}
