<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Contracts\NotificationClient;
use Psr\Log\LoggerInterface;

/** Used when no notification service is configured. */
final class LogNotificationClient implements NotificationClient
{
    public function __construct(private readonly LoggerInterface $logger) {}

    public function send(string $event, array $recipient, array $payload, string $idempotencyKey): void
    {
        $this->logger->info('notification', ['event' => $event, 'payload' => $payload]);
    }
}
