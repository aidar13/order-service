<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\NotificationClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/** Queued only after the surrounding transaction commits: no e-mails about rolled-back orders. */
final class SendCustomerNotification implements ShouldQueue, ShouldQueueAfterCommit
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public int $tries = 5;

    /**
     * @param array{email?: string, phone?: string} $recipient
     * @param array<string, scalar|null> $payload
     */
    public function __construct(
        public readonly string $event,
        private readonly array $recipient,
        private readonly array $payload,
        private readonly string $idempotencyKey,
    ) {
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function handle(NotificationClient $client): void
    {
        $client->send($this->event, $this->recipient, $this->payload, $this->idempotencyKey);
    }
}
