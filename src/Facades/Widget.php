<?php

namespace FluxErp\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \FluxErp\Widgets\WidgetManager
 */
class Widget extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'flux.widget_manager';
    }

    public static function register(string $name, string $widget): void
    {
        static::$app->make('flux.widget_manager')->register($name, $widget);
    }
}
