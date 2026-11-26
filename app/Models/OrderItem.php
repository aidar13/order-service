<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $sku
 * @property string $name
 * @property int $unit_price_cents
 * @property int $quantity
 */
class OrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['order_id', 'sku', 'name', 'unit_price_cents', 'quantity'];

    protected function casts(): array
    {
        return ['unit_price_cents' => 'integer', 'quantity' => 'integer'];
    }
}
