<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'number' => 'ORD-'.fake()->unique()->numerify('########'),
            'customer_id' => 1,
            'customer_email' => 'customer@example.test',
            'customer_phone' => null,
            'status' => OrderStatus::New,
            'delivery_address' => fake()->streetAddress(),
            'total_cents' => 5000,
            'currency' => 'KZT',
        ];
    }
}
