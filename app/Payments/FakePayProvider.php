<?php

declare(strict_types=1);

namespace App\Payments;

use App\Contracts\PaymentProvider;
use App\DTO\PaymentIntentDTO;
use App\DTO\PaymentNotificationDTO;
use App\Exceptions\InvalidWebhook;
use App\Models\Payment;
use Illuminate\Support\Str;

/**
 * Fictional acquirer used for development: the customer is "redirected" to a payment page and the
 * result arrives as a webhook signed with HMAC-SHA256 over the raw body. No card data ever
 * touches this service.
 */
final readonly class FakePayProvider implements PaymentProvider
{
    public const NAME = 'fakepay';

    public function __construct(
        private string $secret,
        private string $checkoutUrl,
    ) {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function initiate(Payment $payment): PaymentIntentDTO
    {
        $externalId = 'fp_' . Str::lower(Str::random(24));

        return new PaymentIntentDTO($externalId, rtrim($this->checkoutUrl, '/') . '/' . $externalId);
    }

    public function sign(string $rawBody): string
    {
        return hash_hmac('sha256', $rawBody, $this->secret);
    }

    public function parseWebhook(string $rawBody, string $signature): PaymentNotificationDTO
    {
        if ($this->secret === '' || !hash_equals($this->sign($rawBody), $signature)) {
            throw InvalidWebhook::signature();
        }

        $data = json_decode($rawBody, true);

        if (
            !is_array($data)
            || !is_string($data['payment_id'] ?? null)
            || !in_array($data['status'] ?? null, ['succeeded', 'failed'], true)
            || !is_int($data['amount_cents'] ?? null)
        ) {
            throw InvalidWebhook::payload('payment_id, status and amount_cents are required');
        }

        return new PaymentNotificationDTO(
            $data['payment_id'],
            $data['status'] === 'succeeded',
            $data['amount_cents'],
            is_string($data['reason'] ?? null) ? $data['reason'] : null,
        );
    }
}
