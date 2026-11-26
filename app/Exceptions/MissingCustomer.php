<?php

declare(strict_types=1);

namespace App\Exceptions;

final class MissingCustomer extends ApiException
{
    public function __construct()
    {
        parent::__construct('Customer identity is missing.');
    }

    public function httpStatus(): int
    {
        return 401;
    }
}
