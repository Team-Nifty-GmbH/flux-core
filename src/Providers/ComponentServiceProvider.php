<?php

namespace FluxErp\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;
use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
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
            $directoryPath = $directoryPath ?:
                Str::replace(['\\', 'FluxErp'], ['/', flux_path('src')], $namespace);
            $components = [];
            $phpFiles = Finder::create()
                ->in($directoryPath)
                ->name('*.php')
                ->files();

            foreach ($phpFiles as $phpFile) {
                /** @var SplFileInfo $phpFile */
                $relativePath = Str::of($phpFile->getRealPath())
                    ->replace($directoryPath, '')
                    ->replace(DIRECTORY_SEPARATOR, '\\');

                $class = $namespace . str_replace(
                    '/',
                    '\\',
                    pathinfo($relativePath, PATHINFO_FILENAME)
                );

                if (class_exists($class)) {
                    $exploded = explode('\\', $relativePath);
                    array_walk($exploded, function (&$value): void {
                        $value = Str::of($value)->remove('.php')->snake('-');
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
            $components = [];
            $phpFiles = Finder::create()
                ->in(flux_path('resources/views/components'))
                ->name('*.blade.php')
                ->files();

            foreach ($phpFiles as $phpFile) {
                /** @var SplFileInfo $phpFile */
                $relativePath = Str::of($phpFile->getRealPath())
                    ->replace(flux_path('resources/views/components'), '')
                    ->replace(DIRECTORY_SEPARATOR, '.')
                    ->remove('.blade.php')
                    ->afterLast('views.components');

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
