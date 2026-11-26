<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendCustomerNotification;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

final class PaymentTest extends ApiTestCase
{
    /** @param array<string, mixed> $order */
    private function pay(array $order, ?string $key = null): string
    {
        $headers = $this->asCustomer() + ($key === null ? [] : ['Idempotency-Key' => $key]);

        return (string) $this->postJson("/api/orders/{$order['id']}/pay", ['provider' => 'fakepay'], $headers)
            ->assertOk()
            ->assertJsonPath('data.status', 'pending')
            ->json('data.id');
    }

    public function test_payment_returns_a_payment_page(): void
    {
        $order = $this->createOrder();

        $this->postJson("/api/orders/{$order['id']}/pay", ['provider' => 'fakepay'], $this->asCustomer())
            ->assertOk()
            ->assertJsonPath('data.amount_cents', 70000)
            ->assertJsonPath('data.payment_url', fn (string $url) => str_starts_with($url, 'https://pay.fakepay.example/checkout/fp_'));
    }

    public function test_repeated_requests_do_not_create_second_payment(): void
    {
        $order = $this->createOrder();

        $first = $this->pay($order, 'key-1');
        $this->assertSame($first, $this->pay($order, 'key-1'));
        $this->assertSame($first, $this->pay($order));
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_successful_webhook_marks_order_paid_and_notifies_once(): void
    {
        $order = $this->createOrder();
        $payment = $this->pay($order);
        Queue::fake();

        $this->webhook($payment, 70000)->assertOk()->assertJsonPath('status', 'succeeded');
        $this->webhook($payment, 70000)->assertOk()->assertJsonPath('status', 'succeeded');

        $this->getJson("/api/orders/{$order['id']}", $this->asCustomer())->assertJsonPath('data.status', 'paid');
        Queue::assertPushed(SendCustomerNotification::class, 1);
        Queue::assertPushed(SendCustomerNotification::class, fn (SendCustomerNotification $job) => $job->event === 'order.paid');
    }

    public function test_declined_payment_leaves_order_payable(): void
    {
        $order = $this->createOrder();
        $first = $this->pay($order, 'a');

        $this->webhook($first, 70000, 'failed')->assertOk()->assertJsonPath('status', 'failed');

        $this->assertNotSame($first, $this->pay($order, 'b'));
        $this->assertDatabaseHas('orders', ['id' => $order['id'], 'status' => 'new']);
    }

    public function test_forged_signature_is_rejected(): void
    {
        $payment = $this->pay($this->createOrder());

        $this->webhook($payment, 70000, 'succeeded', 'forged')->assertUnauthorized();

        $this->assertSame('pending', Payment::query()->firstOrFail()->status->value);
    }

    public function test_amount_mismatch_and_unknown_payment_are_rejected(): void
    {
        $payment = $this->pay($this->createOrder());

        $this->webhook($payment, 1)->assertUnprocessable();
        $this->webhook('00000000-0000-0000-0000-000000000000', 70000)->assertUnprocessable();
    }

    public function test_money_for_a_cancelled_order_is_flagged_for_refund(): void
    {
        $order = $this->createOrder();
        $payment = $this->pay($order);
        $this->postJson("/api/orders/{$order['id']}/cancel", [], $this->asCustomer())->assertOk();

        $this->webhook($payment, 70000)->assertOk()->assertJsonPath('status', 'refund_required');

        $this->assertDatabaseHas('orders', ['id' => $order['id'], 'status' => 'cancelled']);
    }

    public function test_paid_order_cannot_be_paid_or_cancelled(): void
    {
        $order = $this->createOrder();
        $this->webhook($this->pay($order), 70000);

        $this->postJson("/api/orders/{$order['id']}/pay", ['provider' => 'fakepay'], $this->asCustomer())->assertStatus(409);
        $this->postJson("/api/orders/{$order['id']}/cancel", [], $this->asCustomer())->assertStatus(409);
    }

    public function test_unknown_provider(): void
    {
        $order = $this->createOrder();

        $this->postJson("/api/orders/{$order['id']}/pay", ['provider' => 'nope'], $this->asCustomer())->assertUnprocessable();
    }

    public function test_notification_is_posted_to_the_notification_service(): void
    {
        config(['orders.notifications.url' => 'http://notifications.test', 'orders.notifications.api_key' => 'n-key']);
        Http::fake(['notifications.test/*' => Http::response([], 202)]);

        $order = $this->createOrder();

        Http::assertSent(fn ($r) => $r['event'] === 'order.created'
            && $r['recipient'] === ['email' => 'buyer@example.test']
            && $r['idempotency_key'] === 'order.created:'.$order['number']);
    }

    public function test_console_simulator_pays_an_order(): void
    {
        $order = $this->createOrder();
        $payment = $this->pay($order);

        $this->artisan('payments:simulate', ['payment' => $payment])->assertSuccessful();

        $this->assertDatabaseHas('orders', ['id' => $order['id'], 'status' => 'paid']);
    }
}
