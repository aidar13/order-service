<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\OrderRepository;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentOrderRepository implements OrderRepository
{
    public function create(array $attributes, array $items): Order
    {
        $order = Order::query()->create($attributes);
        $order->items()->createMany($items);

        return $order->load('items', 'payments');
    }

    public function findForCustomer(int $orderId, int $customerId): ?Order
    {
        return Order::query()->with('items', 'payments')->where('customer_id', $customerId)->find($orderId);
    }

    public function lock(int $orderId): Order
    {
        return Order::query()->lockForUpdate()->findOrFail($orderId);
    }

    public function paginateForCustomer(int $customerId, int $perPage): LengthAwarePaginator
    {
        return Order::query()
            ->with('items', 'payments')
            ->where('customer_id', $customerId)
            ->latest('id')
            ->paginate($perPage);
    }

    public function save(Order $order): void
    {
        $order->save();
    }
}
