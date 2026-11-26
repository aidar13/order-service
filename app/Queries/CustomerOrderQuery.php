<?php

declare(strict_types=1);

namespace App\Queries;

use App\Contracts\OrderRepository;
use App\DTO\Customer;
use App\Exceptions\OrderNotFound;
use App\Models\Order;

/** The only way to load an order on behalf of a customer, so ownership cannot be forgotten. */
final class CustomerOrderQuery
{
    public function __construct(private readonly OrderRepository $orders) {}

    public function get(int $orderId, Customer $customer): Order
    {
        return $this->orders->findForCustomer($orderId, $customer->id) ?? throw new OrderNotFound;
    }
}
