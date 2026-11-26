<?php

declare(strict_types=1);

namespace App\Exceptions;

final class UnknownProvider extends ApiException
{
    public function __construct(string $name)
    {
        parent::__construct("Unknown payment provider '{$name}'.");
    }

    public function httpStatus(): int
    {
        return 404;
    }
}
