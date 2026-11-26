<?php

declare(strict_types=1);

namespace App\Bus;

use App\Contracts\Command;
use App\Contracts\CommandBus;
use Illuminate\Contracts\Bus\Dispatcher;

/** Adapter over Laravel's bus; the command → handler map lives in CommandBusServiceProvider. */
final class LaravelCommandBus implements CommandBus
{
    public function __construct(private readonly Dispatcher $dispatcher) {}

    public function dispatch(Command $command): mixed
    {
        return $this->dispatcher->dispatchSync($command);
    }
}
