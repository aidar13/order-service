<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\OrderCreatedEvent;
use App\Events\OrderPaidEvent;
use App\Listeners\NotifyCustomerListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * The explicit map of events to listeners. Auto-discovery is switched off in
 * bootstrap/app.php, otherwise every listener would be registered twice.
 */
final class EventServiceProvider extends ServiceProvider
{
    /** @var array<class-string, list<class-string>> */
    protected $listen = [
        OrderCreatedEvent::class => [
            NotifyCustomerListener::class,
        ],
        OrderPaidEvent::class => [
            NotifyCustomerListener::class,
        ],
    ];

    /** The framework's own event provider has already done this. */
    protected function configureEmailVerification(): void
    {
    }
}
