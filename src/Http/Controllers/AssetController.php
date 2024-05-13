<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Facades\Asset;
use FluxErp\Livewire\Contact\Communication;
use FluxErp\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
use Livewire\Drawer\Utils;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function Livewire\invade;

class AssetController extends Controller
{
    public function asset(string $file)
    {
        $path = Asset::path($file);

        if (! is_file($path) || ! file_exists($path)) {
            abort(404);
        }

        if (invade(app(\Illuminate\Foundation\Vite::class))->isCssPath($path)) {
            $mimeType = 'text/css';
        } else {
            $mimeType = match (pathinfo($path, PATHINFO_EXTENSION)) {
                'js' => 'application/javascript',
                default => File::mimeType($path),
            };
        }

        return Utils::pretendResponseIsFile($path, $mimeType);
    }

    public function favicon(): BinaryFileResponse
    {
        return response()->file(flux_path('public/pwa/images/icons-vector.svg'));
    }

    public function mailPixel(?Communication $communication = null)
    {
        if ($communication->exists && ! auth()->check()) {
            activity('communication')
                ->performedOn($communication)
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->event('mail_opened')
                ->log($communication->subject . ' Mail opened');
        }

        $logo = resolve_static(Client::class, 'default')->getFirstMedia('logo_small');

        return Utils::pretendResponseIsFile(
            $logo->getPath(),
            $logo->mime_type
        );
    }

    public function manifest(): JsonResponse
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

    public function pwaServiceWorker(): Response
    {
        return response(Vite::content('resources/js/sw.js', flux_path('public/build')))
            ->header('Content-Type', 'application/javascript');
    }
}
