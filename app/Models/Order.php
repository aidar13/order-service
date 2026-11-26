<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use Carbon\CarbonImmutable;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $number
 * @property int $customer_id
 * @property string|null $customer_email
 * @property string|null $customer_phone
 * @property OrderStatus $status
 * @property string $delivery_address
 * @property int $total_cents
 * @property string $currency
 * @property CarbonImmutable|null $paid_at
 * @property CarbonImmutable $created_at
 * @property-read Collection<int, OrderItem> $items
 * @property-read Collection<int, Payment> $payments
 */
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'number', 'customer_id', 'customer_email', 'customer_phone', 'status',
        'delivery_address', 'total_cents', 'currency', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'status' => OrderStatus::class,
            'total_cents' => 'integer',
            'paid_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('id');
    }
}
