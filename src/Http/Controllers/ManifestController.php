<?php

namespace FluxErp\Http\Controllers;

class ManifestController extends Controller
{
    public function __invoke()
    {
        return response()->json(
            data: [
                'name' => config('app.name'),
                'short_name' => config('app.name'),
                'start_url' => '/',
                'display' => 'standalone',
                'scope' => '/',
                'icons' => [
                    [
                        'src' => '/favicon.svg',
                        'type' => 'image/svg+xml',
                        'sizes' => 'any',
                    ],
                    [
                        'src' => '/flux/pwa/images/icons-192.png',
                        'type' => 'image/png',
                        'sizes' => '192x192',
                    ],
                    [
                        'src' => '/flux/pwa/images/icons-512.png',
                        'type' => 'image/png',
                        'sizes' => '512x512',
                    ],
                ],
            ],
            options: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}
