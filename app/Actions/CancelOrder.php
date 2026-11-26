<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\OrderRepository;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderState;
use App\Models\Order;
use Illuminate\Database\ConnectionInterface;

final class CancelOrder
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly ConnectionInterface $db,
    ) {}

    public function execute(Order $order): Order
    {
        return $this->db->transaction(function () use ($order): Order {
            // Locked re-read: a payment webhook may be marking the same order as paid right now.
            $locked = $this->orders->lock($order->id);

            if (! $locked->status->canTransitionTo(OrderStatus::Cancelled)) {
                throw InvalidOrderState::cannot('cancel', $locked->status);
            }

            $locked->status = OrderStatus::Cancelled;
            $this->orders->save($locked);

            return $locked->load('items', 'payments');
        });
    }
}
