<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class CreateOrderDataDTO
{
    /** @param list<OrderLineDTO> $lines */
    public function __construct(
        public CustomerDTO $customer,
        public string $deliveryAddress,
        public array $lines,
    ) {
    }
}
