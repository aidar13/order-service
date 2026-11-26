<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepository
{
    /** @return Collection<int, Product> */
    public function allActive(): Collection;

    /**
     * @param list<string> $skus
     * @return \Illuminate\Support\Collection<string, Product> keyed by SKU
     */
    public function activeBySkus(array $skus): \Illuminate\Support\Collection;
}
