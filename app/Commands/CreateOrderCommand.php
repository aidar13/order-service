<?php

declare(strict_types=1);

namespace App\Commands;

use App\Contracts\Command;
use App\DTO\CreateOrderData;
use App\Models\Order;

/** @implements Command<Order> */
final readonly class CreateOrderCommand implements Command
{
    public function __construct(
        public CreateOrderData $data,
    ) {}
}
