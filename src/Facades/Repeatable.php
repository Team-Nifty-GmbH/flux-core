<?php

namespace FluxErp\Facades;

use FluxErp\Console\Scheduling\RepeatableManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(string $name, string $class)
 * @method static \Illuminate\Support\Collection all()
 * @method static array|null get(string $name)
 * @method static void autoDiscover(string|null $directory = null, string|null $namespace = null)
 * @method static array getDiscoveries()
 *
 * @see RepeatableManager
 */
class Repeatable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RepeatableManager::class;
    }
}
