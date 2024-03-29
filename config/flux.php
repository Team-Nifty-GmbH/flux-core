<?php

return [
    'install_done' => env('FLUX_INSTALL_DONE', false),

    'license_key' => env('FLUX_LICENSE_KEY'),

    'portal_domain' => env(
        'PORTAL_DOMAIN',
        'portal.' . preg_replace('(^https?://)', '', env('APP_URL'))
    ),
    'rate_limit' => env('API_RATE_LIMIT', 60),

    'media' => [
        'conversion' => env('MEDIA_CONVERSIONS_DISK', 'public'),
        'disk' => env('MEDIA_DISK', 'local'),
    ],
];
