<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_admin_can_login(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'boss@example.com', 'password' => 'secret123']);

        $this->postJson('/api/v1/admin/login', [
            'email' => 'boss@example.com',
            'password' => 'secret123',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', User::ROLE_ADMIN)
            ->assertJsonStructure(['data' => ['token']]);
    }

    public function test_customer_cannot_login_as_admin(): void
    {
        $customer = User::factory()->create(['email' => 'cust@example.com', 'password' => 'secret123']);

        $this->postJson('/api/v1/admin/login', [
            'email' => 'cust@example.com',
            'password' => 'secret123',
        ])->assertStatus(403);
    }

    public function test_customer_cannot_access_admin_routes(): void
    {
        $customer = User::factory()->create();
        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/admin/dashboard')->assertStatus(403);
        $this->getJson('/api/v1/admin/products')->assertStatus(403);
    }

    public function test_admin_dashboard_stats(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => User::factory(),
            'payment_status' => Order::PAYMENT_STATUS_SUCCESSFUL,
            'order_status' => Order::ORDER_STATUS_PAID,
            'total' => 1000,
        ]);

        $response = $this->getJson('/api/v1/admin/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.total_orders', 1)
            ->assertJsonPath('data.paid_orders', 1)
            ->assertJsonPath('data.total_products', 1)
            ->assertJsonPath('data.total_customers', 1)
            ->assertJsonPath('data.revenue', 1000);
    }

    public function test_admin_can_create_and_update_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/admin/products', [
            'name' => 'New Product',
            'category_id' => $category->id,
            'price' => 1500,
            'description' => 'A nice product',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Product');

        $product = Product::where('name', 'New Product')->first();

        $this->patchJson("/api/v1/admin/products/{$product->id}", ['price' => 2000])
            ->assertOk()
            ->assertJsonPath('data.price', 2000);
    }

    public function test_admin_can_delete_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);
        $product = Product::factory()->create();

        $this->deleteJson("/api/v1/admin/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_admin_can_create_a_category(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/admin/categories', ['name' => 'Electronics'])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Electronics');
    }

    public function test_admin_can_transition_order_status_and_notify_customer(): void
    {
        Http::fake();

        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $customer = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'payment_status' => Order::PAYMENT_STATUS_SUCCESSFUL,
            'order_status' => Order::ORDER_STATUS_PAID,
        ]);

        $this->patchJson("/api/v1/admin/orders/{$order->id}/status", ['order_status' => Order::ORDER_STATUS_PROCESSING])
            ->assertOk()
            ->assertJsonPath('data.order_status', Order::ORDER_STATUS_PROCESSING);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => Order::class,
        ]);
    }

    public function test_admin_cannot_perform_invalid_order_transition(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);
        $order = Order::factory()->create(['order_status' => Order::ORDER_STATUS_PENDING]);

        $this->patchJson("/api/v1/admin/orders/{$order->id}/status", ['order_status' => Order::ORDER_STATUS_DELIVERED])
            ->assertStatus(422);
    }

    public function test_admin_lists_customers(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);
        User::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/admin/customers');

        $response->assertOk()
            ->assertJsonCount(2, 'data.items');
    }

    public function test_super_admin_can_create_another_admin(): void
    {
        $superAdmin = User::factory()->admin(User::ROLE_SUPER_ADMIN)->create();
        Sanctum::actingAs($superAdmin);

        $this->postJson('/api/v1/admin/users', [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_ADMIN,
        ])->assertStatus(201)
            ->assertJsonPath('data.role', User::ROLE_ADMIN);
    }
}
