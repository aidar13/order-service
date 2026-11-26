<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\NotificationClient;
use App\Contracts\OrderRepository;
use App\Contracts\PaymentRepository;
use App\Contracts\ProductRepository;
use App\DTO\CustomerDTO;
use App\Exceptions\MissingCustomer;
use App\Handlers\CreateOrderHandler;
use App\Notifications\HttpNotificationClient;
use App\Notifications\LogNotificationClient;
use App\Payments\FakePayProvider;
use App\Payments\ProviderRegistry;
use App\Repositories\EloquentOrderRepository;
use App\Repositories\EloquentPaymentRepository;
use App\Repositories\EloquentProductRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Factory as Http;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepository::class, EloquentProductRepository::class);
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
        $this->app->bind(PaymentRepository::class, EloquentPaymentRepository::class);

        // Resolved per request, after TrustGateway has verified who is talking to us.
        $this->app->bind(CustomerDTO::class, function (Application $app): CustomerDTO {
            $request = $app->make(Request::class);
            $id      = (string)$request->header('X-User', '');

            if (!ctype_digit($id)) {
                throw new MissingCustomer;
            }

            return new CustomerDTO(
                (int)$id,
                $request->header('X-User-Email') ?: null,
                $request->header('X-User-Phone') ?: null,
            );
        });

        $this->app->when(CreateOrderHandler::class)->needs('$currency')->give(fn(): string => (string)config('orders.currency'));

        $this->app->bind(FakePayProvider::class, fn() => new FakePayProvider(
            (string)config('orders.payments.fakepay.secret'),
            (string)config('orders.payments.fakepay.checkout_url'),
        ));
        $this->app->tag([FakePayProvider::class], 'payment.providers');
        $this->app->bind(ProviderRegistry::class, fn(Application $app) => new ProviderRegistry($app->tagged('payment.providers')));

        $this->app->bind(NotificationClient::class, function (Application $app): NotificationClient {
            $url = (string)config('orders.notifications.url');

            return $url === ''
                ? new LogNotificationClient($app->make(LoggerInterface::class))
                : new HttpNotificationClient(
                    $app->make(Http::class),
                    $url,
                    (string)config('orders.notifications.api_key'),
                );
        });
    }
}
