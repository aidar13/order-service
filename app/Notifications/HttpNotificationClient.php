<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Contracts\NotificationClient;
use Illuminate\Http\Client\Factory as Http;

final readonly class HttpNotificationClient implements NotificationClient
{
    public function __construct(
        private Http $http,
        private string $baseUrl,
        private string $apiKey,
    ) {
    }

    public function send(string $event, array $recipient, array $payload, string $idempotencyKey): void
    {
        $this->http
            ->withHeaders(['X-Api-Key' => $this->apiKey])
            ->timeout(5)
            ->acceptJson()
            ->post(rtrim($this->baseUrl, '/') . '/api/notifications', [
                'event'           => $event,
                'recipient'       => $recipient,
                'payload'         => $payload,
                'idempotency_key' => $idempotencyKey,
            ])
            ->throw();
    }
}
