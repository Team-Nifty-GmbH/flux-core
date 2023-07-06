<?php

namespace FluxErp\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(string $name, string $action)
 * @method static \Illuminate\Support\Collection all()
 * @method static array|null get(string $name)
 * @method static \Illuminate\Support\Collection getByModel(string $model)
 * @method static void autoDiscover(string|null $directory = null, string|null $namespace = null)
 *
 * @see \FluxErp\Actions\ActionManager
 */
class Action extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'flux.action_manager';
    }
}
