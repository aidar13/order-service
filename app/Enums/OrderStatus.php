<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case New = 'new';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $next): bool
    {
        return $this === self::New && $next !== self::New;
    }
}
