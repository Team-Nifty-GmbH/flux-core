<?php

namespace FluxErp\Facades;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Facade;

/**
 * @see \FluxErp\Menu\MenuManager
 */
class Menu extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'flux.menu_manager';
    }

    public static function register(
        Route $route,
        string $label = null,
        string $icon = null,
        int $order = null,
    ): void {
        static::$app->make('flux.menu_manager')->register($route, $label, $icon, $order);
    }
}
