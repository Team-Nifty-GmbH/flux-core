<?php

namespace FluxErp\Settings;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use ReflectionClass;
use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\SettingsContainer;
use Symfony\Component\Finder\Finder;

class SettingsManager
{
    public function all(): Collection
    {
        return app(SettingsContainer::class)->getSettingClasses();
    }

    public function autoDiscover(?string $directory = null, ?string $namespace = null): void
    {
        $namespace = $namespace ?: 'App\\Settings';
        $path = $directory ?: app_path('Settings');

        if (! is_dir($path)) {
            return;
        }

        if (! app()->runningInConsole()
            && file_exists(config('settings.discovered_settings_cache_path') . '/settings.php')
        ) {
            return;
        }

        $iterator = Finder::create()
            ->in($path)
            ->files()
            ->name('*.php')
            ->sortByName();

        foreach ($iterator as $file) {
            $relativePath = ltrim(str_replace($path, '', $file->getPath()), DIRECTORY_SEPARATOR);
            $subNameSpace = ! empty($relativePath)
                ? str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath) . '\\'
                : '';
            $class = $namespace . '\\' . $subNameSpace . $file->getBasename('.php');

            if (
                ! class_exists($class)
                || ! is_a($class, Settings::class, true)
                || (new ReflectionClass($class))->isAbstract()
            ) {
                continue;
            }

            $this->register($class);
        }
    }

    public function register(string $settings): void
    {
        if (! is_a($settings, Settings::class, true) || (new ReflectionClass($settings))->isAbstract()) {
            throw new InvalidArgumentException('The provided settings class is not a valid settings class');
        }

        if (in_array($settings, config('settings.settings', []), true)) {
            return;
        }

        config()->push('settings.settings', $settings);
    }
}
