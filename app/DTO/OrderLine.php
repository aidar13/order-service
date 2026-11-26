<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class OrderLine
{
    public function __construct(
        public string $sku,
        public int $quantity,
    ) {}
}
