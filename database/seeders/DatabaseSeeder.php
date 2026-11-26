<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $catalogue = [
            'BOX-M' => ['Cardboard box M', 35000],
            'PALLET-EUR' => ['Euro pallet', 450000],
            'WRAP-FILM' => ['Stretch film roll', 220000],
        ];

        foreach ($catalogue as $sku => [$name, $price]) {
            Product::query()->updateOrCreate(['sku' => $sku], ['name' => $name, 'price_cents' => $price]);
        }
    }
}
