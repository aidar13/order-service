<?php

declare(strict_types=1);

namespace App\Contracts;

interface CommandBus
{
    /**
     * Runs the handler of the command synchronously.
     *
     * @template TResult
     *
     * @param Command<TResult> $command
     * @return TResult
     */
    public function dispatch(Command $command): mixed;
}
