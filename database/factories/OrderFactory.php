<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 500, 200000);

        return [
            'user_id' => User::factory(),
            'order_number' => 'GOBE-' . strtoupper(fake()->unique()->bothify('????####')),
            'subtotal' => $subtotal,
            'delivery_fee' => 0,
            'total' => $subtotal,
            'payment_status' => Order::PAYMENT_STATUS_PENDING,
            'order_status' => Order::ORDER_STATUS_PENDING,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => Order::PAYMENT_STATUS_SUCCESSFUL,
            'order_status' => Order::ORDER_STATUS_PAID,
        ]);
    }
}
