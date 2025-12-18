<?php

namespace FluxErp\Facades;

use FluxErp\Widgets\WidgetManager;
use Illuminate\Support\Facades\Facade;

/**
 * WidgetManager Class
 *
 * The WidgetManager class is responsible for managing and registering Livewire widgets in FluxErp.
 * It provides methods to register, unregister, and discover widgets automatically from a given directory.
 * Widgets are Livewire components that implement the UserWidget contract and can be used to render various UI elements.
 *
 * @method static void register(string $name, string $widget)
 * @method static void unregister(string $name)
 * @method static array all()
 * @method static string|null get(string $name, array $defaultAttributes = [])
 * @method static void autoDiscoverWidgets(string $directory = null, string $namespace = null)
 *
 * @see WidgetManager
 */
class Widget extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WidgetManager::class;
    }
}
