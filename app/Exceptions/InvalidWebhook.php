<?php

declare(strict_types=1);

namespace App\Exceptions;

final class InvalidWebhook extends ApiException
{
    private function __construct(string $message, private readonly int $status)
    {
        parent::__construct($message);
    }

    public static function signature(): self
    {
        return new self('Invalid webhook signature.', 401);
    }

    public static function payload(string $reason): self
    {
        return new self("Invalid webhook payload: {$reason}.", 422);
    }

    public function httpStatus(): int
    {
        return $this->status;
    }
}
