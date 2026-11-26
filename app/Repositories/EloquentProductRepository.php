<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\ProductRepository;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

final class EloquentProductRepository implements ProductRepository
{
    public function allActive(): Collection
    {
        return Product::query()->where('is_active', true)->orderBy('sku')->get();
    }

    public function activeBySkus(array $skus): \Illuminate\Support\Collection
    {
        return Product::query()->where('is_active', true)->whereIn('sku', $skus)->get()->keyBy('sku');
    }
}
