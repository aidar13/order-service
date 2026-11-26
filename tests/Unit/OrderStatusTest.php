<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\OrderStatus;
use PHPUnit\Framework\TestCase;

final class OrderStatusTest extends TestCase
{
    public function test_only_new_orders_move(): void
    {
        $this->assertTrue(OrderStatus::New->canTransitionTo(OrderStatus::Paid));
        $this->assertTrue(OrderStatus::New->canTransitionTo(OrderStatus::Cancelled));
        $this->assertFalse(OrderStatus::Paid->canTransitionTo(OrderStatus::Cancelled));
        $this->assertFalse(OrderStatus::Cancelled->canTransitionTo(OrderStatus::Paid));
    }
}
