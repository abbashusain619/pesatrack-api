<?php

return [
    'default_provider' => env('PAYMENT_PROVIDER', 'mock'),
    'providers' => [
        'azampay' => [
            'sandbox' => env('AZAMPAY_SANDBOX', true),
            'client_id' => env('AZAMPAY_CLIENT_ID'),
            'client_secret' => env('AZAMPAY_CLIENT_SECRET'),
            'api_key' => env('AZAMPAY_API_KEY'),
            'base_url' => env('AZAMPAY_BASE_URL', 'https://sandbox.azampay.co.tz'),
        ],
    ],
];