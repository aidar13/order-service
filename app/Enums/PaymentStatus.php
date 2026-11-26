<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';

    /** Money arrived for an order that can no longer accept it. */
    case RefundRequired = 'refund_required';

    public function isFinal(): bool
    {
        return $this !== self::Pending;
    }
}
