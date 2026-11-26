<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\CommandBusServiceProvider;
use App\Providers\EventServiceProvider;

return [
    AppServiceProvider::class,
    CommandBusServiceProvider::class,
    EventServiceProvider::class,
];
