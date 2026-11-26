<?php

declare(strict_types=1);

namespace App\Handlers;

use App\Commands\StartPaymentCommand;
use App\Contracts\OrderRepository;
use App\Contracts\PaymentRepository;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\InvalidOrderState;
use App\Models\Payment;
use App\Payments\ProviderRegistry;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;

/** Idempotent: retries with the same key, or a second click while one is pending, never double-charge. */
final class StartPaymentHandler
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly PaymentRepository $payments,
        private readonly ProviderRegistry $providers,
        private readonly ConnectionInterface $db,
    ) {}

    public function handle(StartPaymentCommand $command): Payment
    {
        $order = $command->order;
        $providerName = $command->providerName;
        $idempotencyKey = $command->idempotencyKey;

        $provider = $this->providers->get($providerName);

        return $this->db->transaction(function () use ($order, $provider, $idempotencyKey): Payment {
            $locked = $this->orders->lock($order->id);

            if ($idempotencyKey !== null) {
                $previous = $this->payments->findByIdempotencyKey($locked->id, $idempotencyKey);
                if ($previous !== null) {
                    return $previous;
                }
            }

            if ($locked->status !== OrderStatus::New) {
                throw InvalidOrderState::cannot('pay', $locked->status);
            }

            $pending = $this->payments->findPending($locked->id, $provider->name());
            if ($pending !== null) {
                return $pending;
            }

            $payment = $this->payments->create([
                'public_id' => (string) Str::uuid(),
                'order_id' => $locked->id,
                'provider' => $provider->name(),
                'status' => PaymentStatus::Pending,
                'amount_cents' => $locked->total_cents,
                'idempotency_key' => $idempotencyKey,
            ]);

            $intent = $provider->initiate($payment);
            $payment->external_id = $intent->externalId;
            $payment->payment_url = $intent->paymentUrl;
            $this->payments->save($payment);

            return $payment;
        });
    }
}
