<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Jobs\SendCustomerNotification;

/** One listener for every customer-facing order event (DRY). */
final class NotifyCustomer
{
    public function handle(OrderCreated|OrderPaid $event): void
    {
        $order = $event->order;
        $recipient = array_filter(['email' => $order->customer_email, 'phone' => $order->customer_phone]);

        if ($recipient === []) {
            return;
        }

        $name = $event instanceof OrderPaid ? 'order.paid' : 'order.created';

        SendCustomerNotification::dispatch(
            $name,
            $recipient,
            [
                'order_number' => $order->number,
                'total' => number_format($order->total_cents / 100, 2, '.', ' '),
                'currency' => $order->currency,
            ],
            "{$name}:{$order->number}",
        );
    }
}
