<?php

declare(strict_types=1);

namespace App\Exceptions;

final class UnknownProduct extends ApiException
{
    /** @param list<string> $skus */
    public function __construct(array $skus)
    {
        parent::__construct('Unknown or unavailable products: ' . implode(', ', $skus) . '.');
    }

    public function httpStatus(): int
    {
        return 422;
    }
}
