<?php

namespace FluxErp\Facades;

use FluxErp\Support\Container\ProductTypeManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(string $name, string|null $class = null, string|null $view = null, bool $default = false)
 * @method static void unregister(string $name)
 * @method static \Illuminate\Support\Collection all()
 * @method static array|null get(string|null $name)
 * @method static array|null getDefault()
 *
 * @see ProductTypeManager
 */
class ProductType extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ProductTypeManager::class;
    }
}
