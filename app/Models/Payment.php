<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $public_id
 * @property int $order_id
 * @property string $provider
 * @property PaymentStatus $status
 * @property int $amount_cents
 * @property string|null $external_id
 * @property string|null $payment_url
 * @property string|null $idempotency_key
 * @property string|null $failure_reason
 */
class Payment extends Model
{
    protected $fillable = [
        'public_id', 'order_id', 'provider', 'status', 'amount_cents',
        'external_id', 'payment_url', 'idempotency_key', 'failure_reason',
    ];

    protected function casts(): array
    {
        return ['status' => PaymentStatus::class, 'amount_cents' => 'integer'];
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
