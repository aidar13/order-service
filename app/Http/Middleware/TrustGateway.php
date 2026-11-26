<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Identity headers are only meaningful if the request provably came through the gateway. */
final class TrustGateway
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('orders.internal_api_key');

        if ($expected === '' || ! hash_equals($expected, (string) $request->header('X-Api-Key', ''))) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
