<?php

declare(strict_types=1);

namespace App\Payments;

use App\Contracts\PaymentProvider;
use App\DTO\PaymentIntent;
use App\DTO\PaymentNotification;
use App\Exceptions\InvalidWebhook;
use App\Models\Payment;
use Illuminate\Support\Str;

/**
 * Fictional acquirer used for development: the customer is "redirected" to a payment page and the
 * result arrives as a webhook signed with HMAC-SHA256 over the raw body. No card data ever
 * touches this service.
 */
final class FakePayProvider implements PaymentProvider
{
    public const NAME = 'fakepay';

    public function __construct(
        private readonly string $secret,
        private readonly string $checkoutUrl,
    ) {}

    public function name(): string
    {
        return self::NAME;
    }

    public function initiate(Payment $payment): PaymentIntent
    {
        $externalId = 'fp_'.Str::lower(Str::random(24));

        return new PaymentIntent($externalId, rtrim($this->checkoutUrl, '/').'/'.$externalId);
    }

    public function sign(string $rawBody): string
    {
        return hash_hmac('sha256', $rawBody, $this->secret);
    }

    public function parseWebhook(string $rawBody, string $signature): PaymentNotification
    {
        if ($this->secret === '' || ! hash_equals($this->sign($rawBody), $signature)) {
            throw InvalidWebhook::signature();
        }

        $data = json_decode($rawBody, true);

        if (
            ! is_array($data)
            || ! is_string($data['payment_id'] ?? null)
            || ! in_array($data['status'] ?? null, ['succeeded', 'failed'], true)
            || ! is_int($data['amount_cents'] ?? null)
        ) {
            throw InvalidWebhook::payload('payment_id, status and amount_cents are required');
        }

        return new PaymentNotification(
            $data['payment_id'],
            $data['status'] === 'succeeded',
            $data['amount_cents'],
            is_string($data['reason'] ?? null) ? $data['reason'] : null,
        );
    }
}
