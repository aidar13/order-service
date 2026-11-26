<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/** Business-rule violations that map to an HTTP status. */
abstract class ApiException extends RuntimeException
{
    abstract public function httpStatus(): int;
}
