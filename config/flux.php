<?php

return [
    'install_done' => env('FLUX_INSTALL_DONE', false),

    'license_key' => env('FLUX_LICENSE_KEY'),

    'formal_salutation' => env('FLUX_FORMAL_SALUTATION', true),

    'portal_domain' => env(
        'PORTAL_DOMAIN',
        'portal.' . preg_replace('(^https?://)', '', env('APP_URL'))
    ),
    'flux_url' => env('FLUX_URL'),

    'rate_limit' => env('API_RATE_LIMIT', 60),

    'media' => [
        'conversion' => env('MEDIA_CONVERSIONS_DISK', 'public'),
        'disk' => env('MEDIA_DISK', 'local'),
    ],

    'vite' => [
        'reverb_app_key' => env('VITE_REVERB_APP_KEY', env('REVERB_APP_KEY')),
        'reverb_host' => env(
            'VITE_REVERB_HOST',
            'ws.' . str_replace(['https://', 'http://'], '', env('APP_URL'))
        ),
        'reverb_port' => env('VITE_REVERB_PORT', 443),
        'reverb_protocol' => env('VITE_REVERB_SCHEME', 'https'),
    ],

    'fcm' => [
        'credentials' => env('FCM_CREDENTIALS_PATH'),
    ],
];
