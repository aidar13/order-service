<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\OrderStatus;

final class InvalidOrderState extends ApiException
{
    public static function cannot(string $action, OrderStatus $status): self
    {
        return new self("Cannot {$action} an order in status '{$status->value}'.");
    }

    public function httpStatus(): int
    {
        return 409;
    }
}
