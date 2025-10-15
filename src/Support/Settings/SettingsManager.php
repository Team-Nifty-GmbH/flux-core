<?php

namespace FluxErp\Support\Settings;

use FluxErp\Settings\FluxSetting;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;

class SettingsManager
{
    protected array $settings = [];

    public function boot(): void
    {
        foreach ($this->settings as $setting) {
            app()->singleton(data_get($setting, 'settings_class'));
            Route::get('/' . data_get($setting, 'route_name'), data_get($setting, 'component_class'))
                ->name('settings.' . data_get($setting, 'route_name'));
        }
    }

    public function all(): array
    {
        return $this->settings;
    }

    public function grouped(): array
    {
        $grouped = [];

        foreach ($this->settings as $setting) {
            $grouped[data_get($setting, 'group') ?? 'general'][] = $setting;
        }

        return $grouped;
    }

    public function register(
        string $settingsClass,
        ?string $label = null,
        ?string $group = null
    ): void {
        if (! is_subclass_of($settingsClass, FluxSetting::class)) {
            throw new InvalidArgumentException(
                'Settings class ' . $settingsClass . ' must extend ' . FluxSetting::class
            );
        }

        $this->settings[] = [
            'settings_class' => $settingsClass,
            'component_class' => resolve_static($settingsClass, 'componentClass'),
            'route_name' => resolve_static($settingsClass, 'routeName'),
            'label' => $label ?? resolve_static($settingsClass, 'label'),
            'group' => $group ?? resolve_static($settingsClass, 'group'),
        ];
    }
}
