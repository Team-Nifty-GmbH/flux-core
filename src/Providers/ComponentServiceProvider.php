<?php

namespace FluxErp\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;
use Throwable;

class ComponentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerLivewireComponents();
        $this->registerBladeComponents();
    }

    protected function registerLivewireComponents(): void
    {
        $livewireNamespace = 'FluxErp\\Livewire\\';

        foreach ($this->getViewClassAliasFromNamespace($livewireNamespace) as $alias => $class) {
            try {
                if (is_a($class, Component::class, true)
                    && ! (new ReflectionClass($class))->isAbstract()
                ) {
                    Livewire::component($alias, $class);
                }
            } catch (Throwable) {
                // Skip invalid components
            }
        }
    }

    protected function getViewClassAliasFromNamespace(string $namespace, ?string $directoryPath = null): array
    {
        if ($namespace === 'FluxErp\\Livewire\\') {
            $cachePath = $this->app->bootstrapPath('cache/flux-livewire-components.php');

            if (file_exists($cachePath)) {
                return require $cachePath;
            }
        }

        return once(function () use ($namespace, $directoryPath) {
            $directoryPath = $directoryPath ?: Str::replace(['\\', 'FluxErp'], ['/', __DIR__], $namespace);
            $directoryIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directoryPath));
            $phpFiles = new RegexIterator($directoryIterator, '/\.php$/');
            $components = [];

            foreach ($phpFiles as $phpFile) {
                $relativePath = Str::replace($directoryPath, '', $phpFile->getRealPath());
                $relativePath = Str::replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
                $class = $namespace . str_replace(
                    '/',
                    '\\',
                    pathinfo($relativePath, PATHINFO_FILENAME)
                );

                if (class_exists($class)) {
                    $exploded = explode('\\', $relativePath);
                    array_walk($exploded, function (&$value): void {
                        $value = Str::snake(Str::remove('.php', $value), '-');
                    });

                    $alias = ltrim(implode('.', $exploded), '.');
                    $components[$alias] = $class;
                }
            }

            return $components;
        });
    }

    protected function registerBladeComponents(): void
    {
        $cachePath = $this->app->bootstrapPath('cache/flux-blade-components.php');

        if (file_exists($cachePath)) {
            $components = require $cachePath;
        } else {
            $directoryIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(__DIR__ . '/../resources/views/components')
            );
            $phpFiles = new RegexIterator($directoryIterator, '/\.blade\.php$/');

            $components = [];
            foreach ($phpFiles as $phpFile) {
                $relativePath = Str::replace(__DIR__ . '/../resources/views/components/', '', $phpFile->getRealPath());
                $relativePath = Str::replace(DIRECTORY_SEPARATOR, '.', Str::remove('.blade.php', $relativePath));
                $relativePath = Str::afterLast($relativePath, 'views.components.');

                $components[] = [
                    'view' => 'flux::components.' . $relativePath,
                    'alias' => Str::remove('.index', $relativePath),
                ];
            }
        }

        foreach ($components as $component) {
            Blade::component($component['view'], $component['alias']);
        }

        Blade::componentNamespace('FluxErp\\View\\Components', 'flux');
    }
}
