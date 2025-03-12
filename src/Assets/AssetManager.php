<?php

namespace FluxErp\Assets;

use FluxErp\Helpers\Vite;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

use function Livewire\invade;

class AssetManager implements Htmlable
{
    use Macroable;

    protected static Collection $assets;

    protected static array $viteManifests = [];

    public function __construct()
    {
        static::$assets = collect();
    }

    public static function clear(): void
    {
        static::$assets = collect();
    }

    public static function path(string $filename): ?string
    {
        $asset = static::$assets->first(
            fn ($asset) => data_get($asset, 'name') === $filename || data_get($asset, 'alias') === $filename
        );

        return data_get($asset ?? [], 'path');
    }

    public function all(): Collection
    {
        return static::$assets;
    }

    public function register(string $name, string $path, array $attributes): void
    {
        if (! is_file($path) || ! file_exists($path)) {
            throw new \Exception("Unable to locate asset file: {$path}");
        }

        static::$assets[$name] = array_merge($attributes, ['path' => $path, 'name' => $name]);
    }

    public function toHtml(string|array|null $items = null): HtmlString
    {
        $html = '';
        $items = Arr::wrap($items);
        $assets = $items ? static::$assets->only($items) : static::$assets;

        $vite = invade(app(Vite::class));
        foreach ($assets->where('is_vite', false) as $asset) {
            if ($vite->isCssPath($asset['path'])) {
                $html .= $vite->makeStyleTagWithAttributes($this->url($asset['name']), []);

                continue;
            }

            $html .= $vite->makeScriptTagWithAttributes($this->url($asset['name']), []);
        }

        foreach (static::$viteManifests as $vite) {
            $vite->entryPoints = array_intersect($vite->entryPoints, $assets->keys()->toArray());
            $html .= $vite->toHtml();
        }

        return new HtmlString($html);
    }

    public function unregister(string $name): void
    {
        static::$assets->forget($name);
    }

    public function url(string $path, ?bool $secure = null): string
    {
        return app('url')->asset(Str::start($path, 'flux-assets/'), $secure);
    }

    public function vite(string $path, string|array $files, string $manifestFilename = 'manifest.json'): void
    {
        $buildDirectory = is_dir($path) ? $path : Str::finish($path, '/') . $manifestFilename;
        $vite = invade(app(Vite::class));
        $files = Arr::wrap($files);

        $vite
            ->useBuildDirectory($buildDirectory)
            ->createAssetPathsUsing(
                fn (string $path, ?bool $secure = null) => $this->url(
                    str_replace(Str::finish($buildDirectory, '/'), '', $path),
                    $secure
                )
            )
            ->withEntryPoints($files);

        static::$viteManifests[$path] = $vite;

        $manifest = $vite->manifest($buildDirectory);

        foreach ($files as $file) {
            $chunk = $vite->chunk($manifest, $file);

            $this->register(
                $chunk['src'],
                Str::finish($buildDirectory, '/') . $chunk['file'],
                [
                    'is_vite' => true,
                    'alias' => $chunk['file'],
                    'src' => $chunk['src'],
                ]
            );

            foreach (data_get($chunk, 'imports', []) as $import) {
                $this->register(
                    $manifest[$import]['file'],
                    Str::finish($buildDirectory, '/') . $manifest[$import]['file'],
                    [
                        'is_vite' => true,
                        'alias' => $manifest[$import]['file'],
                    ]
                );
            }
        }

        foreach ($manifest as $file => $data) {
            if (static::$assets->has($file)) {
                continue;
            }

            $this->register(
                $file,
                Str::finish($buildDirectory, '/') . $data['file'],
                [
                    'is_vite' => true,
                    'alias' => $data['file'],
                ],
            );
        }
    }
}
