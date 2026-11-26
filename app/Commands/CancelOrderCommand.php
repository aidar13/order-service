<?php

declare(strict_types=1);

namespace App\Commands;

use App\Contracts\Command;
use App\Models\Order;

/** @implements Command<Order> */
final readonly class CancelOrderCommand implements Command
{
    public function __construct(
        public Order $order,
    ) {
    }
}
