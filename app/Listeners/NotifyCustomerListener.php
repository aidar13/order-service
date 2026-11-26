<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use App\Events\OrderPaidEvent;
use App\Jobs\SendCustomerNotification;

/** One listener for every customer-facing order event (DRY). */
final class NotifyCustomerListener
{
    public function handle(OrderCreatedEvent|OrderPaidEvent $event): void
    {
        $order     = $event->order;
        $recipient = array_filter(['email' => $order->customer_email, 'phone' => $order->customer_phone]);

        if ($recipient === []) {
            return;
        }

        $name = $event instanceof OrderPaidEvent ? 'order.paid' : 'order.created';

        SendCustomerNotification::dispatch(
            $name,
            $recipient,
            [
                'order_number' => $order->number,
                'total'        => number_format($order->total_cents / 100, 2, '.', ' '),
                'currency'     => $order->currency,
            ],
            "{$name}:{$order->number}",
        );
    }
}
