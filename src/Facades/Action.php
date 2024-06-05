<?php

namespace FluxErp\Facades;

use FluxErp\Actions\ActionManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(string $name, string $action)
 * @method static \Illuminate\Support\Collection all()
 * @method static array|null get(string $name)
 * @method static \Illuminate\Support\Collection getByModel(string $model)
 * @method static void autoDiscover(string|null $directory = null, string|null $namespace = null)
 *
 * @see ActionManager
 */
class Action extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ActionManager::class;
    }
}
