<?php

namespace FluxErp\Facades;

use FluxErp\Menu\MenuManager;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(string|Route $route, string|null $icon = null, string|null $label = null, int|null $order = null, array|null $params = null, string|null $path = null)
 * @method static void group(string $path, string|null $icon = null, string|null $label = null, int|null $order = null, \Closure|null $closure = null)
 * @method static void unregister(string $name)
 * @method static array all()
 * @method static array|null get(string $name)
 * @method static array forGuard(string $guard, string $group = null, bool $ignorePermissions = false)
 *
 * @see MenuManager
 */
class Menu extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MenuManager::class;
    }
}
