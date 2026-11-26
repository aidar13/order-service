<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTO\PaymentIntentDTO;
use App\DTO\PaymentNotificationDTO;
use App\Exceptions\InvalidWebhook;
use App\Models\Payment;

/** Adapter of an acquiring provider. A new provider = one class + one line in the service provider. */
interface PaymentProvider
{
    public function name(): string;

    public function initiate(Payment $payment): PaymentIntentDTO;

    /**
     * Verifies authenticity and translates the provider's message.
     *
     * @throws InvalidWebhook
     */
    public function parseWebhook(string $rawBody, string $signature): PaymentNotificationDTO;
}
