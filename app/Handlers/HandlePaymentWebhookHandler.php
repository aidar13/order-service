<?php

declare(strict_types=1);

namespace App\Handlers;

use App\Commands\HandlePaymentWebhookCommand;
use App\Contracts\OrderRepository;
use App\Contracts\PaymentRepository;
use App\DTO\PaymentNotificationDTO;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderPaidEvent;
use App\Exceptions\InvalidWebhook;
use App\Models\Payment;
use App\Payments\ProviderRegistry;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;

/** Providers deliver webhooks at least once, so processing a duplicate must be a no-op. */
final readonly class HandlePaymentWebhookHandler
{
    public function __construct(
        private ProviderRegistry $providers,
        private PaymentRepository $payments,
        private OrderRepository $orders,
        private Dispatcher $events,
        private ConnectionInterface $db,
    ) {
    }

    public function handle(HandlePaymentWebhookCommand $command): Payment
    {
        $providerName = $command->providerName;
        $rawBody      = $command->rawBody;
        $signature    = $command->signature;

        $notification = $this->providers->get($providerName)->parseWebhook($rawBody, $signature);

        return $this->db->transaction(function () use ($providerName, $notification): Payment {
            $payment = $this->payments->lockByPublicId($notification->paymentId);

            if ($payment === null || $payment->provider !== $providerName) {
                throw InvalidWebhook::payload('unknown payment');
            }

            if ($payment->status->isFinal()) {
                return $payment;
            }

            if ($notification->amountCents !== $payment->amount_cents) {
                throw InvalidWebhook::payload('amount does not match');
            }

            $notification->succeeded ? $this->succeed($payment) : $this->fail($payment, $notification);

            $this->payments->save($payment);

            return $payment;
        });
    }

    private function succeed(Payment $payment): void
    {
        $order = $this->orders->lock($payment->order_id);

        if (!$order->status->canTransitionTo(OrderStatus::Paid)) {
            // Cancelled or already paid by another attempt: keep the money trail for a refund.
            $payment->status = PaymentStatus::RefundRequired;

            return;
        }

        $payment->status = PaymentStatus::Succeeded;
        $order->status   = OrderStatus::Paid;
        $order->paid_at  = now()->toImmutable();
        $this->orders->save($order);

        $this->events->dispatch(new OrderPaidEvent($order));
    }

    private function fail(Payment $payment, PaymentNotificationDTO $notification): void
    {
        $payment->status         = PaymentStatus::Failed;
        $payment->failure_reason = $notification->failureReason;
    }
}
