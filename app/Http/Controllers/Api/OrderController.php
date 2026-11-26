<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\CancelOrder;
use App\Actions\CreateOrder;
use App\Actions\GetCustomerOrder;
use App\Actions\StartPayment;
use App\Contracts\OrderRepository;
use App\DTO\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\PayOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

final class OrderController extends Controller
{
    #[OA\Get(
        path: '/api/orders',
        summary: 'Own orders, newest first',
        security: [['Bearer' => []]],
        tags: ['Orders'],
        parameters: [new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', maximum: 100))],
        responses: [new OA\Response(response: 200, description: 'Paginated orders')],
    )]
    public function index(Request $request, Customer $customer, OrderRepository $orders): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return OrderResource::collection($orders->paginateForCustomer($customer->id, $perPage));
    }

    #[OA\Post(
        path: '/api/orders',
        summary: 'Create an order; prices are taken from the catalogue',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['delivery_address', 'items'],
            properties: [
                new OA\Property(property: 'delivery_address', type: 'string', example: '12 Lake St'),
                new OA\Property(property: 'items', type: 'array', items: new OA\Items(properties: [
                    new OA\Property(property: 'sku', type: 'string', example: 'BOX-M'),
                    new OA\Property(property: 'quantity', type: 'integer', example: 10),
                ])),
            ],
        )),
        tags: ['Orders'],
        responses: [
            new OA\Response(response: 201, description: 'Created', content: new OA\JsonContent(ref: '#/components/schemas/Order')),
            new OA\Response(response: 422, description: 'Validation error / unknown product'),
        ],
    )]
    public function store(StoreOrderRequest $request, Customer $customer, CreateOrder $action): JsonResponse
    {
        return (new OrderResource($action->execute($request->toDto($customer))))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/orders/{id}',
        summary: 'An own order with items and payments',
        security: [['Bearer' => []]],
        tags: ['Orders'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Order', content: new OA\JsonContent(ref: '#/components/schemas/Order')),
            new OA\Response(response: 404, description: 'Not found'),
        ],
    )]
    public function show(int $id, Customer $customer, GetCustomerOrder $orders): OrderResource
    {
        return new OrderResource($orders->execute($id, $customer));
    }

    #[OA\Post(
        path: '/api/orders/{id}/cancel',
        summary: 'Cancel an unpaid order',
        security: [['Bearer' => []]],
        tags: ['Orders'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Cancelled'), new OA\Response(response: 409, description: 'Already paid or cancelled')],
    )]
    public function cancel(int $id, Customer $customer, GetCustomerOrder $orders, CancelOrder $action): OrderResource
    {
        return new OrderResource($action->execute($orders->execute($id, $customer)));
    }

    #[OA\Post(
        path: '/api/orders/{id}/pay',
        summary: 'Start a payment; returns the provider payment page. Send Idempotency-Key to retry safely',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'provider', type: 'string', example: 'fakepay'),
        ])),
        tags: ['Payments'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Payment', content: new OA\JsonContent(ref: '#/components/schemas/Payment')),
            new OA\Response(response: 409, description: 'Order cannot be paid'),
        ],
    )]
    public function pay(int $id, PayOrderRequest $request, Customer $customer, GetCustomerOrder $orders, StartPayment $action): PaymentResource
    {
        $payment = $action->execute($orders->execute($id, $customer), $request->provider(), $request->idempotencyKey());
        // Same response for a new payment and for an idempotent replay.
        $payment->wasRecentlyCreated = false;

        return new PaymentResource($payment);
    }
}
