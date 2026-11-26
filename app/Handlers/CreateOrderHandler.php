<?php

declare(strict_types=1);

namespace App\Handlers;

use App\Commands\CreateOrderCommand;
use App\Contracts\OrderRepository;
use App\Contracts\ProductRepository;
use App\DTO\OrderLine;
use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Exceptions\UnknownProduct;
use App\Models\Order;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;

final class CreateOrderHandler
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly ProductRepository $products,
        private readonly Dispatcher $events,
        private readonly ConnectionInterface $db,
        private readonly string $currency,
    ) {}

    public function handle(CreateOrderCommand $command): Order
    {
        $data = $command->data;

        $skus = array_map(static fn (OrderLine $l): string => $l->sku, $data->lines);
        $products = $this->products->activeBySkus($skus);

        $missing = array_values(array_filter($skus, static fn (string $sku): bool => ! $products->has($sku)));
        if ($missing !== []) {
            throw new UnknownProduct($missing);
        }

        $items = [];
        $total = 0;
        foreach ($data->lines as $line) {
            // Prices come from the catalogue only; the client never sends money amounts.
            $product = $products->get($line->sku) ?? throw new UnknownProduct([$line->sku]);
            $items[] = [
                'sku' => $product->sku,
                'name' => $product->name,
                'unit_price_cents' => $product->price_cents,
                'quantity' => $line->quantity,
            ];
            $total += $product->price_cents * $line->quantity;
        }

        return $this->db->transaction(function () use ($data, $items, $total): Order {
            $order = $this->orders->create([
                'number' => 'ORD-'.Str::upper(Str::random(10)),
                'customer_id' => $data->customer->id,
                'customer_email' => $data->customer->email,
                'customer_phone' => $data->customer->phone,
                'status' => OrderStatus::New,
                'delivery_address' => $data->deliveryAddress,
                'total_cents' => $total,
                'currency' => $this->currency,
            ], $items);

            $this->events->dispatch(new OrderCreated($order));

            return $order;
        });
    }
}
