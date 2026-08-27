<?php

namespace Database\Factories;

use App\Models\DeliveryInformation;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryInformation>
 */
class DeliveryInformationFactory extends Factory
{
    protected $model = DeliveryInformation::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'recipient_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'additional_notes' => null,
        ];
    }
}
