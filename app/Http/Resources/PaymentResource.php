<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Payment */
#[OA\Schema(schema: 'Payment', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'provider', type: 'string', example: 'fakepay'),
    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'succeeded', 'failed', 'refund_required']),
    new OA\Property(property: 'amount_cents', type: 'integer'),
    new OA\Property(property: 'payment_url', type: 'string', nullable: true),
    new OA\Property(property: 'failure_reason', type: 'string', nullable: true),
])]
final class PaymentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->public_id,
            'provider'       => $this->provider,
            'status'         => $this->status->value,
            'amount_cents'   => $this->amount_cents,
            'payment_url'    => $this->payment_url,
            'failure_reason' => $this->failure_reason,
        ];
    }
}
