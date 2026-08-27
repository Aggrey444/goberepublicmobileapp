<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\DeliveryInformation;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function actingCustomer(): array
    {
        $user = User::factory()->create();

        return [$user, $this->actingAs($user, 'sanctum')];
    }

    private function withCart(User $user, array $items): void
    {
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        foreach ($items as $item) {
            $cart->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }
    }

    public function test_checkout_with_nested_delivery_information(): void
    {
        Http::fake();

        [$user, $client] = $this->actingCustomer();
        $product = Product::factory()->create(['price' => 3000]);
        $this->withCart($user, [['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 3000]]);

        $response = $client->postJson('/api/v1/orders', [
            'delivery_information' => [
                'recipient_name' => 'Jane Doe',
                'phone' => '08012345678',
                'address' => '12 Test Street',
                'city' => 'Lagos',
                'additional_notes' => 'Leave at the gate',
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total', 6000)
            ->assertJsonPath('data.order_status', Order::ORDER_STATUS_PAYMENT_PENDING)
            ->assertJsonPath('data.delivery_information.recipient_name', 'Jane Doe')
            ->assertJsonStructure(['data' => ['order_number', 'items']]);

        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'total' => 6000]);
        $this->assertCount(1, DeliveryInformation::where('order_id', $response->json('data.id'))->get());
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_generates_unique_order_number(): void
    {
        Http::fake();

        [$user, $client] = $this->actingCustomer();
        $product = Product::factory()->create(['price' => 100]);
        $this->withCart($user, [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100]]);

        $response = $client->postJson('/api/v1/orders', [
            'delivery_information' => [
                'recipient_name' => 'Jane Doe',
                'phone' => '08012345678',
                'address' => '12 Test Street',
            ],
        ]);

        $orderNumber = $response->json('data.order_number');
        $this->assertMatchesRegularExpression('/^GOBE-[A-Z0-9]{8}$/', $orderNumber);
    }

    public function test_checkout_requires_non_empty_cart(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders', [
                'delivery_information' => [
                    'recipient_name' => 'Jane Doe',
                    'phone' => '08012345678',
                    'address' => '12 Test Street',
                ],
            ])
            ->assertStatus(422);
    }

    public function test_checkout_creates_a_notification(): void
    {
        Http::fake();

        [$user, $client] = $this->actingCustomer();
        $product = Product::factory()->create(['price' => 500]);
        $this->withCart($user, [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 500]]);

        $client->postJson('/api/v1/orders', [
            'delivery_information' => [
                'recipient_name' => 'Jane Doe',
                'phone' => '08012345678',
                'address' => '12 Test Street',
            ],
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => Order::class,
        ]);
    }

    public function test_customer_orders_are_private(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other, 'sanctum')
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertStatus(403);
    }

    public function test_lists_customer_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);
        Order::factory()->create(['user_id' => User::factory()]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/orders');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }
}
