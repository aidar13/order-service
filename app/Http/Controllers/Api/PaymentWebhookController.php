<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\HandlePaymentWebhook;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class PaymentWebhookController extends Controller
{
    #[OA\Post(
        path: '/api/payments/webhook/{provider}',
        summary: 'Payment result from a provider; authenticated by the X-Signature header (HMAC of the raw body)',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'payment_id', type: 'string', format: 'uuid'),
            new OA\Property(property: 'status', type: 'string', enum: ['succeeded', 'failed']),
            new OA\Property(property: 'amount_cents', type: 'integer'),
        ])),
        tags: ['Payments'],
        parameters: [new OA\Parameter(name: 'provider', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Processed (also for duplicates)'),
            new OA\Response(response: 401, description: 'Bad signature'),
            new OA\Response(response: 422, description: 'Unknown payment / amount mismatch'),
        ],
        x: ['unauthenticated' => true],
    )]
    public function __invoke(string $provider, Request $request, HandlePaymentWebhook $action): JsonResponse
    {
        $payment = $action->execute($provider, $request->getContent(), (string) $request->header('X-Signature', ''));

        return response()->json(['status' => $payment->status->value]);
    }
}
