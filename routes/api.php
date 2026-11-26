<?php

declare(strict_types=1);

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Middleware\TrustGateway;
use Illuminate\Support\Facades\Route;

// Public: the gateway reads this document to learn which routes exist.
Route::get('openapi.json', static fn () => response()->file(base_path('docs/openapi.json'), ['Content-Type' => 'application/json']));

Route::middleware(TrustGateway::class)->group(function (): void {
    Route::get('products', [ProductController::class, 'index']);

    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{id}', [OrderController::class, 'show'])->whereNumber('id');
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel'])->whereNumber('id');
    Route::post('orders/{id}/pay', [OrderController::class, 'pay'])->whereNumber('id');

    Route::post('payments/webhook/{provider}', PaymentWebhookController::class);
});
