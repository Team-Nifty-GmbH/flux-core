<?php

namespace FluxErp\Facades;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(Route $route, string $label = null, string $icon = null, int $order = null)
 * @method static void unregister(string $name)
 * @method static array all()
 * @method static array|null get(string $name)
 * @method static array forGuard(string $guard, string $group = null, bool $ignorePermissions = false)
 *
 * @see \FluxErp\Menu\MenuManager
 */
class Menu extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'flux.menu_manager';
    }
}
