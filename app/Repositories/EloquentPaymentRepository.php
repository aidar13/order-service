<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\PaymentRepository;
use App\Enums\PaymentStatus;
use App\Models\Payment;

final class EloquentPaymentRepository implements PaymentRepository
{
    public function create(array $attributes): Payment
    {
        return Payment::query()->create($attributes);
    }

    public function findByIdempotencyKey(int $orderId, string $key): ?Payment
    {
        return Payment::query()->where('order_id', $orderId)->where('idempotency_key', $key)->first();
    }

    public function findPending(int $orderId, string $provider): ?Payment
    {
        return Payment::query()
            ->where('order_id', $orderId)
            ->where('provider', $provider)
            ->where('status', PaymentStatus::Pending)
            ->first();
    }

    public function lockByPublicId(string $publicId): ?Payment
    {
        return Payment::query()->where('public_id', $publicId)->lockForUpdate()->first();
    }

    public function save(Payment $payment): void
    {
        $payment->save();
    }
}
