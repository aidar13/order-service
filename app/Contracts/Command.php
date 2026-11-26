<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * A request to change something. One command is handled by exactly one handler.
 *
 * @template TResult what the handler returns
 */
interface Command {}
