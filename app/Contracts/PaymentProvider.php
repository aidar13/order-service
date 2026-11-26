<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTO\PaymentIntent;
use App\DTO\PaymentNotification;
use App\Exceptions\InvalidWebhook;
use App\Models\Payment;

/** Adapter of an acquiring provider. A new provider = one class + one line in the service provider. */
interface PaymentProvider
{
    public function name(): string;

    public function initiate(Payment $payment): PaymentIntent;

    /**
     * Verifies authenticity and translates the provider's message.
     *
     * @throws InvalidWebhook
     */
    public function parseWebhook(string $rawBody, string $signature): PaymentNotification;
}
