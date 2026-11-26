<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'sku' => strtoupper(fake()->unique()->bothify('???-###')),
            'name' => fake()->words(3, true),
            'price_cents' => fake()->numberBetween(100, 100000),
            'is_active' => true,
        ];
    }
}
