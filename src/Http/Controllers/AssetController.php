<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Facades\Asset;
use FluxErp\Models\Communication;
use FluxErp\Models\Tenant;
use FluxErp\Providers\ViewServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function avatar(Request $request): Response
    {
        $color = $request->input('color', '6366f1');
        $text = $request->input('text', '');

        $svg = '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="12" fill="#' . $color . '"/>
            <text x="12" y="12" text-anchor="middle" dominant-baseline="central"
                  fill="white" font-size="12" font-weight="600" font-family="system-ui, -apple-system, sans-serif">
                ' . htmlspecialchars($text) . '
            </text>
        </svg>';

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
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
                ->event('communication_opened')
                ->log($communication->subject . ' opened');
        }

        $logo = resolve_static(Tenant::class, 'default')->getFirstMedia('logo_small');

        if ($logo && file_exists($logo->getPath())) {
            $path = $logo->getPath();
            $mimeType = $logo->mime_type;
        } else {
            $path = tempnam(sys_get_temp_dir(), 'mailpixel_svg_');
            $mimeType = 'image/svg+xml';
            file_put_contents($path, '<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"></svg>');
        }

        return Utils::pretendResponseIsFile(
            $path,
            $mimeType
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
                        'src' => '/pwa-icons/icons-192.png',
                        'type' => 'image/png',
                        'sizes' => '192x192',
                    ],
                    [
                        'src' => '/pwa-icons/icons-512.png',
                        'type' => 'image/png',
                        'sizes' => '512x512',
                    ],
                ],
            ],
            options: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    public function pwaIcon(string $file): BinaryFileResponse
    {
        abort_if(! File::exists(flux_path('public/pwa/images/' . $file)), 404);

        return response()->file(flux_path('public/pwa/images/' . $file));
    }

    public function pwaServiceWorker(): Response
    {
        return response(
            Vite::content(
                ViewServiceProvider::getRealPackageAssetPath(
                    'resources/js/sw.js',
                    'team-nifty-gmbh/flux-erp'
                ),
                'build'
            )
        )
            ->header('Content-Type', 'application/javascript')
            ->header('Cache-Control', 'max-age=3600');
    }
}
