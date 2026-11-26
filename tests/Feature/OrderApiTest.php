<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendCustomerNotification;
use App\Models\Product;
use Illuminate\Support\Facades\Queue;

final class OrderApiTest extends ApiTestCase
{
    public function test_requests_that_bypass_the_gateway_are_rejected(): void
    {
        $this->getJson('/api/orders', ['X-User' => '7'])->assertUnauthorized();
        $this->getJson('/api/orders', ['X-User' => '7', 'X-Api-Key' => 'wrong'])->assertUnauthorized();
    }

    public function test_orders_need_a_customer_identity(): void
    {
        $this->getJson('/api/orders', ['X-Api-Key' => 'test-key'])->assertUnauthorized();
    }

    public function test_catalogue_is_available_to_anonymous_gateway_calls(): void
    {
        $this->product();

        $this->getJson('/api/products', ['X-Api-Key' => 'test-key'])->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_total_is_calculated_from_the_catalogue(): void
    {
        Queue::fake();
        $this->product('BOX-M', 35000);
        $this->product('PALLET', 450000);

        $this->postJson('/api/orders', [
            'delivery_address' => '1 Test St',
            'items' => [
                ['sku' => 'BOX-M', 'quantity' => 3, 'price_cents' => 1],
                ['sku' => 'PALLET', 'quantity' => 1],
            ],
        ], $this->asCustomer())
            ->assertCreated()
            ->assertJsonPath('data.total_cents', 555000)
            ->assertJsonPath('data.items.0.unit_price_cents', 35000)
            ->assertJsonPath('data.status', 'new');

        Queue::assertPushed(SendCustomerNotification::class, fn (SendCustomerNotification $job) => $job->event === 'order.created');
    }

    public function test_price_change_does_not_rewrite_existing_orders(): void
    {
        $order = $this->createOrder();
        Product::query()->update(['price_cents' => 1]);

        $this->getJson("/api/orders/{$order['id']}", $this->asCustomer())->assertJsonPath('data.total_cents', 70000);
    }

    public function test_unknown_or_inactive_product_is_rejected(): void
    {
        Product::factory()->create(['sku' => 'OLD', 'is_active' => false]);

        $this->postJson('/api/orders', ['delivery_address' => 'A', 'items' => [['sku' => 'OLD', 'quantity' => 1]]], $this->asCustomer())
            ->assertUnprocessable();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_validation(): void
    {
        $this->postJson('/api/orders', ['items' => [['sku' => 'X', 'quantity' => 0]]], $this->asCustomer())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['delivery_address', 'items.0.quantity']);
    }

    public function test_customers_see_only_their_own_orders(): void
    {
        $order = $this->createOrder(7);

        $this->getJson('/api/orders', $this->asCustomer(8))->assertJsonCount(0, 'data');
        $this->getJson("/api/orders/{$order['id']}", $this->asCustomer(8))->assertNotFound();
        $this->postJson("/api/orders/{$order['id']}/cancel", [], $this->asCustomer(8))->assertNotFound();
        $this->getJson('/api/orders', $this->asCustomer(7))->assertJsonCount(1, 'data');
    }

    public function test_cancel_once(): void
    {
        $order = $this->createOrder();

        $this->postJson("/api/orders/{$order['id']}/cancel", [], $this->asCustomer())->assertOk()->assertJsonPath('data.status', 'cancelled');
        $this->postJson("/api/orders/{$order['id']}/cancel", [], $this->asCustomer())->assertStatus(409);
    }

    public function test_openapi_document_is_public(): void
    {
        $this->get('/api/openapi.json')->assertOk();
    }
}
