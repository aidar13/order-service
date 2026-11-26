<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<array<string, mixed>>  $items
     */
    public function create(array $attributes, array $items): Order;

    public function findForCustomer(int $orderId, int $customerId): ?Order;

    /** Row lock; call inside a transaction. */
    public function lock(int $orderId): Order;

    /** @return LengthAwarePaginator<int, Order> */
    public function paginateForCustomer(int $customerId, int $perPage): LengthAwarePaginator;

    public function save(Order $order): void;
}
