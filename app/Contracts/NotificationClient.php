<?php

declare(strict_types=1);

namespace App\Contracts;

/** Port to the notification microservice. */
interface NotificationClient
{
    /**
     * @param  array{email?: string, phone?: string}  $recipient
     * @param  array<string, scalar|null>  $payload
     */
    public function send(string $event, array $recipient, array $payload, string $idempotencyKey): void;
}
