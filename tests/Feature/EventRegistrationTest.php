<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\OrderCreatedEvent;
use App\Events\OrderPaidEvent;
use App\Jobs\SendCustomerNotification;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class EventRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** Exactly once per event: a second registration would e-mail the customer twice. */
    public function test_every_order_event_queues_exactly_one_notification(): void
    {
        $order = Order::factory()->create();
        Queue::fake();

        Event::dispatch(new OrderCreatedEvent($order));
        Queue::assertPushed(SendCustomerNotification::class, 1);

        Event::dispatch(new OrderPaidEvent($order));
        Queue::assertPushed(SendCustomerNotification::class, 2);
    }
}
