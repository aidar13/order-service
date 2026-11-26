<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class CreateOrderData
{
    /** @param list<OrderLine> $lines */
    public function __construct(
        public Customer $customer,
        public string $deliveryAddress,
        public array $lines,
    ) {}
}
