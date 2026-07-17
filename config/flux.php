<?php

return [
    'inject_assets' => env('FLUX_INJECT_ASSETS', true),

    'install_done' => env('FLUX_INSTALL_DONE', false),

    'license_key' => env('FLUX_LICENSE_KEY'),

    'formal_salutation' => env('FLUX_FORMAL_SALUTATION', true),

    'flux_url' => env('FLUX_URL'),

    'rate_limit' => env('API_RATE_LIMIT', 60),

    'display_timezone' => env('FLUX_DISPLAY_TIMEZONE'),

    'media' => [
        'conversion' => env('MEDIA_CONVERSIONS_DISK', 'public'),
        'disk' => env('MEDIA_DISK', 'local'),
    ],

    'file_uploads' => [
        // Maximum size for chunked uploads. Accepts strings like "1G", "500M".
        'max_size' => env('FLUX_FILE_UPLOAD_MAX_SIZE', '1G'),

        // Validation rules applied to the assembled file at finalize time.
        // Falls back to `livewire.temporary_file_upload.rules` when null.
        'chunk_rules' => null,
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
