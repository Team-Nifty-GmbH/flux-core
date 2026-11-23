<?php

namespace FluxErp\Facades;

use FluxErp\Settings\SettingsManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static void autoDiscover(string|null $directory = null, string|null $namespace = null)
 * @method static void register(string $settings)
 *
 * @see SettingsManager
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SettingsManager::class;
    }
}
