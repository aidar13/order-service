<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Order Service API',
    description: 'Orders and payments. Called through the API gateway, which authenticates users.',
)]
#[OA\SecurityScheme(securityScheme: 'Bearer', type: 'http', scheme: 'bearer')]
abstract class Controller {}
