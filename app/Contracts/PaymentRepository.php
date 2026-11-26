<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Payment;

interface PaymentRepository
{
    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Payment;

    public function findByIdempotencyKey(int $orderId, string $key): ?Payment;

    public function findPending(int $orderId, string $provider): ?Payment;

    /** Row lock; call inside a transaction. */
    public function lockByPublicId(string $publicId): ?Payment;

    public function save(Payment $payment): void;
}
