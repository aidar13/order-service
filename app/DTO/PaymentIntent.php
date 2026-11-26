<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class PaymentIntent
{
    public function __construct(
        public string $externalId,
        public ?string $paymentUrl,
    ) {}
}
