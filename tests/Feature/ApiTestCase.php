<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Payments\FakePayProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> headers exactly as the gateway sends them */
    protected function asCustomer(int $id = 7, ?string $email = 'buyer@example.test'): array
    {
        return array_filter(['X-Api-Key' => 'test-key', 'X-User' => (string) $id, 'X-User-Email' => (string) $email]);
    }

    protected function product(string $sku = 'BOX-M', int $price = 35000): Product
    {
        return Product::factory()->create(['sku' => $sku, 'price_cents' => $price]);
    }

    /** @return array<string, mixed> */
    protected function createOrder(int $customerId = 7): array
    {
        if (! Product::query()->where('sku', 'BOX-M')->exists()) {
            $this->product();
        }

        /** @var array<string, mixed> $data */
        $data = $this->postJson('/api/orders', [
            'delivery_address' => '1 Test St',
            'items' => [['sku' => 'BOX-M', 'quantity' => 2]],
        ], $this->asCustomer($customerId))->assertCreated()->json('data');

        return $data;
    }

    /** @return TestResponse<Response> */
    protected function webhook(string $paymentId, int $amount, string $status = 'succeeded', ?string $signature = null): TestResponse
    {
        $body = (string) json_encode(['payment_id' => $paymentId, 'status' => $status, 'amount_cents' => $amount]);

        return $this->call('POST', '/api/payments/webhook/fakepay', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_API_KEY' => 'test-key',
            'HTTP_X_SIGNATURE' => $signature ?? $this->app->make(FakePayProvider::class)->sign($body),
        ], $body);
    }
}
