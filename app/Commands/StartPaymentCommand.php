<?php

declare(strict_types=1);

namespace App\Commands;

use App\Contracts\Command;
use App\Models\Order;
use App\Models\Payment;

/** @implements Command<Payment> */
final readonly class StartPaymentCommand implements Command
{
    public function __construct(
        public Order $order,
        public string $providerName,
        public ?string $idempotencyKey,
    ) {
    }
}
