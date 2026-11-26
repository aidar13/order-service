<?php

declare(strict_types=1);

namespace App\Commands;

use App\Contracts\Command;
use App\Models\Payment;

/**
 * The body is passed raw: the signature covers it byte for byte.
 *
 * @implements Command<Payment>
 */
final readonly class HandlePaymentWebhookCommand implements Command
{
    public function __construct(
        public string $providerName,
        public string $rawBody,
        public string $signature,
    ) {}
}
