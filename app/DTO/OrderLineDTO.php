<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class OrderLineDTO
{
    public function __construct(
        public string $sku,
        public int $quantity,
    ) {
    }
}
