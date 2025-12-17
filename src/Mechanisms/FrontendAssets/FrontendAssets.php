<?php

namespace FluxErp\Mechanisms\FrontendAssets;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;
use Livewire\Drawer\Utils;

class FrontendAssets
{
    protected static ?array $manifest = null;

    protected static string $buildPath;

    protected static array $registeredManifests = [];

    protected static ?array $mergedManifest = null;

    public bool $hasRenderedScripts = false;

    public bool $hasRenderedStyles = false;

    public static function fluxStyles(): string
    {
        return <<<'PHP'
            <?php echo \FluxErp\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>
        PHP;
    }

    public static function fluxScripts(): string
    {
        return <<<'PHP'
            <?php echo \FluxErp\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>
        PHP;
    }

    public static function styles(): HtmlString
    {
        $instance = app(static::class);
        $instance->hasRenderedStyles = true;

        $html = '';

        $manifest = static::getManifest();
        $cssEntries = [
            'resources/css/app.css',
        ];

        foreach ($cssEntries as $entry) {
            if (isset($manifest[$entry])) {
                $file = $manifest[$entry]['file'];
                $url = route('flux.assets.file', ['file' => $file]);
                $html .= '<link rel="stylesheet" href="' . $url . '">' . "\n";
            }
        }

        foreach (static::$registeredManifests as $packageName => $config) {
            $packageManifest = static::getPackageManifest($packageName);

            foreach (data_get($config, 'entries') ?? [] as $entry) {
                if (! str_ends_with($entry, '.css')) {
                    continue;
                }

                if (isset($packageManifest[$entry])) {
                    $file = $packageManifest[$entry]['file'];
                    $url = route('flux.assets.package', ['package' => $packageName, 'file' => $file]);
                    $html .= '<link rel="stylesheet" href="' . $url . '">' . "\n";
                }
            }
        }

        $html .= view()->yieldPushContent('flux-styles');

        return new HtmlString($html);
    }

    public static function scripts(): HtmlString
    {
        $instance = app(static::class);
        $instance->hasRenderedScripts = true;

        $manifest = static::getManifest();
        $html = '';

        // Single bundled JS file
        $jsEntries = ['resources/js/app.js'];

        $isAuthenticated = auth()->guard('web')->check();
        if ($isAuthenticated) {
            $jsEntries[] = 'resources/js/web-push.js';
        }

        foreach ($jsEntries as $entry) {
            if (! isset($manifest[$entry])) {
                continue;
            }

            $file = $manifest[$entry]['file'];
            $url = route('flux.assets.file', ['file' => $file]);
            $html .= '<script type="module" src="' . $url . '"></script>' . "\n";
        }

        // Load registered package scripts
        foreach (static::$registeredManifests as $packageName => $config) {
            $packageManifest = static::getPackageManifest($packageName);

            foreach ($config['entries'] as $entry) {
                if (! str_ends_with($entry, '.js')) {
                    continue;
                }

                if (isset($packageManifest[$entry])) {
                    $file = $packageManifest[$entry]['file'];
                    $url = route('flux.assets.package', ['package' => $packageName, 'file' => $file]);
                    $html .= '<script type="module" src="' . $url . '"></script>' . "\n";
                }
            }
        }

        if ($isAuthenticated) {
            $html .= <<<'SCRIPT'
            <script type="module">
                document.addEventListener(
                    'livewire:navigated',
                    () => {
                        if (window.Echo && window.Echo.join) {
                            window.Echo.join('presence');
                        }
                    },
                    { once: true },
                );
            </script>
            SCRIPT;
            $html .= "\n";
        }

        $html .= view()->yieldPushContent('flux-scripts');

        return new HtmlString($html);
    }

    public static function getManifest(): array
    {
        if (static::$manifest !== null) {
            return static::$manifest;
        }

        $manifestPath = static::$buildPath . '/manifest.json';

        if (! file_exists($manifestPath)) {
            static::$manifest = [];

            return static::$manifest;
        }

        static::$manifest = json_decode(file_get_contents($manifestPath), true) ?? [];

        return static::$manifest;
    }

    public static function getPackageManifest(string $packageName): array
    {
        if (! isset(static::$registeredManifests[$packageName])) {
            return [];
        }

        $config = static::$registeredManifests[$packageName];
        $manifestPath = $config['path'] . '/manifest.json';

        if (! file_exists($manifestPath)) {
            return [];
        }

        return json_decode(file_get_contents($manifestPath), true) ?? [];
    }

    public function boot(): void
    {
        static::$buildPath = dirname(__DIR__, 3) . '/public/build';

        $this->registerRoutes();
        $this->registerBladeDirectives();
    }

    public function registerManifest(string $name, string $path, array $entries = []): void
    {
        static::$registeredManifests[$name] = [
            'path' => rtrim($path, '/'),
            'entries' => $entries,
        ];

        static::$mergedManifest = null;
    }

    public function getRegisteredPackages(): array
    {
        return array_keys(static::$registeredManifests);
    }

    public function returnCssAsFile()
    {
        $manifest = static::getManifest();
        $entry = $manifest['resources/css/app.css'] ?? null;

        if (! $entry) {
            abort(404);
        }

        $path = static::$buildPath . '/' . $entry['file'];

        return $this->pretendResponseIsFile($path, 'text/css; charset=utf-8');
    }

    public function returnJsAsFile()
    {
        $manifest = static::getManifest();
        $entry = $manifest['resources/js/app.js'] ?? null;

        if (! $entry) {
            abort(404);
        }

        $path = static::$buildPath . '/' . $entry['file'];

        return $this->pretendResponseIsFile($path, 'application/javascript; charset=utf-8');
    }

    public function returnAssetFile(string $file)
    {
        $file = ltrim($file, '/');

        if (str_contains($file, '..')) {
            abort(404);
        }

        $path = static::$buildPath . '/' . $file;

        if (! is_file($path) || ! file_exists($path)) {
            abort(404);
        }

        return $this->pretendResponseIsFile($path, $this->getMimeType($path));
    }

    public function returnPackageAssetFile(string $package, string $file)
    {
        if (! isset(static::$registeredManifests[$package])) {
            abort(404);
        }

        $file = ltrim($file, '/');

        if (str_contains($file, '..')) {
            abort(404);
        }

        $path = static::$registeredManifests[$package]['path'] . '/' . $file;

        if (! is_file($path) || ! file_exists($path)) {
            abort(404);
        }

        return $this->pretendResponseIsFile($path, $this->getMimeType($path));
    }

    protected function getMimeType(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return match ($extension) {
            'js' => 'application/javascript; charset=utf-8',
            'css' => 'text/css; charset=utf-8',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'map' => 'application/json',
            default => 'application/octet-stream',
        };
    }

    protected function registerRoutes(): void
    {
        Route::get('/flux/flux.css', fn () => $this->returnCssAsFile())
            ->name('flux.assets.css');

        Route::get('/flux/flux.js', fn () => $this->returnJsAsFile())
            ->name('flux.assets.js');

        Route::get('/flux/packages/{package}/{file}', fn (string $package, string $file) => $this->returnPackageAssetFile($package, $file))
            ->where('file', '.+')
            ->name('flux.assets.package');

        Route::get('/flux/{file}', fn (string $file) => $this->returnAssetFile($file))
            ->where('file', '.+')
            ->name('flux.assets.file');
    }

    protected function registerBladeDirectives(): void
    {
        Blade::directive('fluxStyles', [static::class, 'fluxStyles']);
        Blade::directive('fluxScripts', [static::class, 'fluxScripts']);
    }

    protected function pretendResponseIsFile(string $path, string $contentType)
    {
        return Utils::pretendResponseIsFile($path, $contentType);
    }
}
