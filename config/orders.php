<?php

declare(strict_types=1);

return [
    // Secret shared with the API gateway; requests without it are rejected.
    'internal_api_key' => env('INTERNAL_API_KEY', ''),

    'currency' => env('ORDERS_CURRENCY', 'KZT'),

    'payments' => [
        'fakepay' => [
            'secret'       => env('FAKEPAY_WEBHOOK_SECRET', ''),
            'checkout_url' => env('FAKEPAY_CHECKOUT_URL', 'https://pay.fakepay.example/checkout'),
        ],
    ],

    'notifications' => [
        'url'     => env('NOTIFICATION_SERVICE_URL', ''),
        'api_key' => env('NOTIFICATION_SERVICE_API_KEY', ''),
    ],
];
