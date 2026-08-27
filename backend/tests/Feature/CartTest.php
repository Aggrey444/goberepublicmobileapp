<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_cart_is_retrieved(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.item_count', 0)
            ->assertJsonCount(0, 'data.items');
    }

    public function test_adds_item_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 2500]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2]);

        $response->assertOk()
            ->assertJsonPath('data.item_count', 2)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.product_id', $product->id)
            ->assertJsonPath('data.items.0.line_total', 5000);
    }

    public function test_cannot_add_inactive_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => Product::STATUS_INACTIVE]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1])
            ->assertStatus(422);
    }

    public function test_updates_cart_item_via_put(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000]);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $item = $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 1000,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/cart/items/{$item->id}", ['quantity' => 5]);

        $response->assertOk()
            ->assertJsonPath('data.items.0.quantity', 5);
    }

    public function test_removes_cart_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000]);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $item = $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 1000,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/cart/items/{$item->id}");

        $response->assertOk()
            ->assertJsonCount(0, 'data.items');
    }

    public function test_clears_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000]);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 1000,
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonCount(0, 'data.items');
    }

    public function test_a_users_cart_is_private(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000]);

        $cart = Cart::factory()->create(['user_id' => $userA->id]);
        $item = $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 1000,
        ]);

        $this->actingAs($userB, 'sanctum')
            ->deleteJson("/api/v1/cart/items/{$item->id}")
            ->assertStatus(404);
    }
}
