<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\InvalidWebhook;
use App\Payments\FakePayProvider;
use PHPUnit\Framework\TestCase;

final class FakePayProviderTest extends TestCase
{
    private function provider(string $secret = 's3'): FakePayProvider
    {
        return new FakePayProvider($secret, 'https://pay.example');
    }

    public function test_parses_a_signed_message(): void
    {
        $body = '{"payment_id":"p1","status":"failed","amount_cents":10,"reason":"declined"}';

        $notification = $this->provider()->parseWebhook($body, $this->provider()->sign($body));

        $this->assertFalse($notification->succeeded);
        $this->assertSame('declined', $notification->failureReason);
    }

    public function test_rejects_tampered_body(): void
    {
        $signature = $this->provider()->sign('{"amount_cents":10}');

        $this->expectException(InvalidWebhook::class);

        $this->provider()->parseWebhook('{"amount_cents":99}', $signature);
    }

    public function test_refuses_to_work_without_a_secret(): void
    {
        $this->expectException(InvalidWebhook::class);

        $this->provider('')->parseWebhook('{}', hash_hmac('sha256', '{}', ''));
    }

    public function test_rejects_incomplete_message(): void
    {
        $this->expectException(InvalidWebhook::class);

        $this->provider()->parseWebhook('{"status":"succeeded"}', $this->provider()->sign('{"status":"succeeded"}'));
    }
}
