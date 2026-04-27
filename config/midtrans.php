<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'clientKey' => env('MIDTRANS_CLIENT_KEY'),
    'serverKey' => env('MIDTRANS_SERVER_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'isProduction' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
    'isSanitized' => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => env('MIDTRANS_IS_3DS', true),
    'is3ds' => env('MIDTRANS_IS_3DS', true),
    'preferred_payment_type' => env('MIDTRANS_PREFERRED_PAYMENT_TYPE', 'qris'),
    'enabled_payments' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('MIDTRANS_ENABLED_PAYMENTS', 'qris,gopay,shopeepay,bca_va,bni_va,bri_va,permata_va,other_va'))
    ))),
    'public_url' => env('MIDTRANS_PUBLIC_URL'),
];
