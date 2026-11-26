<?php

declare(strict_types=1);

namespace App\DTO;

/** A verified webhook message from a payment provider. */
final readonly class PaymentNotificationDTO
{
    public function __construct(
        public string $paymentId,
        public bool $succeeded,
        public int $amountCents,
        public ?string $failureReason = null,
    ) {
    }
}
