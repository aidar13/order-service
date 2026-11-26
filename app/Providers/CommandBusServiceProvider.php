<?php

declare(strict_types=1);

namespace App\Providers;

use App\Bus\LaravelCommandBus;
use App\Commands\CancelOrderCommand;
use App\Commands\CreateOrderCommand;
use App\Commands\HandlePaymentWebhookCommand;
use App\Commands\StartPaymentCommand;
use App\Contracts\CommandBus;
use App\Handlers\CancelOrderHandler;
use App\Handlers\CreateOrderHandler;
use App\Handlers\HandlePaymentWebhookHandler;
use App\Handlers\StartPaymentHandler;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\ServiceProvider;

final class CommandBusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CommandBus::class, LaravelCommandBus::class);
    }

    public function boot(): void
    {
        $this->app->make(Dispatcher::class)->map([
            CreateOrderCommand::class          => CreateOrderHandler::class,
            CancelOrderCommand::class          => CancelOrderHandler::class,
            StartPaymentCommand::class         => StartPaymentHandler::class,
            HandlePaymentWebhookCommand::class => HandlePaymentWebhookHandler::class,
        ]);
    }
}
