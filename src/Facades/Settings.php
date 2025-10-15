<?php

namespace FluxErp\Facades;

use FluxErp\Support\Settings\SettingsManager;
use Illuminate\Support\Facades\Facade;

/**
 * Register and manage application settings
 *
 * @method static void register(string $settingsClass, ?string $label = null, ?string $group = null) Register a settings class (must extend FluxSetting)
 * @method static void boot() Boot all registered settings, create singletons and routes
 * @method static array all() Get all registered settings
 * @method static array grouped() Get all settings grouped by their group name
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
