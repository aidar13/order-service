<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Order */
#[OA\Schema(schema: 'Order', properties: [
    new OA\Property(property: 'id', type: 'integer'),
    new OA\Property(property: 'number', type: 'string'),
    new OA\Property(property: 'status', type: 'string', enum: ['new', 'paid', 'cancelled']),
    new OA\Property(property: 'delivery_address', type: 'string'),
    new OA\Property(property: 'total_cents', type: 'integer'),
    new OA\Property(property: 'currency', type: 'string'),
    new OA\Property(property: 'items', type: 'array', items: new OA\Items(type: 'object')),
    new OA\Property(property: 'payments', type: 'array', items: new OA\Items(ref: '#/components/schemas/Payment')),
])]
final class OrderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status->value,
            'delivery_address' => $this->delivery_address,
            'total_cents' => $this->total_cents,
            'currency' => $this->currency,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'items' => $this->items->map(static fn (OrderItem $i): array => [
                'sku' => $i->sku,
                'name' => $i->name,
                'unit_price_cents' => $i->unit_price_cents,
                'quantity' => $i->quantity,
            ])->all(),
            'payments' => PaymentResource::collection($this->payments),
        ];
    }
}
