<?php

namespace FluxErp\Facades;

use FluxErp\Assets\AssetManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\HtmlString;

/**
 * AssetManager Class
 *
 * The AssetManager registers css and js files to be used in the application.
 *
 * @method static HtmlString toHtml(string|array|null $items = null)
 * @method static void register(string $name, string $path, array $attributes)
 * @method static void unregister(string $name)
 * @method static string url(string $path, bool|null $secure)
 * @method static Collection all()
 * @method static string|null path(string $filename)
 * @method static void clear()
 * @method static void vite(string $path, string|array $files, string $manifestFilename = 'manifest.json')
 *
 * @see AssetManager
 */
class Asset extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AssetManager::class;
    }
}
