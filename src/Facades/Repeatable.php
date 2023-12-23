<?php

namespace FluxErp\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(string $name, string $class)
 * @method static \Illuminate\Support\Collection all()
 * @method static array|null get(string $name)
 * @method static void autoDiscover(string|null $directory = null, string|null $namespace = null)
 *
 * @see \FluxErp\Console\Scheduling\RepeatableManager
 */
class Repeatable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'flux.repeatable_manager';
    }
}
