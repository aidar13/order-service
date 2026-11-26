<?php

declare(strict_types=1);

namespace App\Exceptions;

/** Also thrown for other customers' orders, so their existence is not revealed. */
final class OrderNotFound extends ApiException
{
    public function __construct()
    {
        parent::__construct('Order not found.');
    }

    public function httpStatus(): int
    {
        return 404;
    }
}
