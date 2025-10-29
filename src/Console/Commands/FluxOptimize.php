<?php

namespace FluxErp\Console\Commands;

use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Traits\HasDefault;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;

class FluxOptimize extends Command
{
    protected $description = 'Warms the cache for the Flux application';

    protected bool $forget = false;

    protected $signature = 'flux:optimize';

    public function handle(): void
    {
        $this->optimizeDefaultModels();
        $this->optimizeModelInfo();
        $this->optimizeCommands();
        $this->optimizeViewClasses();
        $this->optimizeWidgets();
        $this->optimizeActions();
        $this->optimizeRepeatable();
        $this->optimizeLivewireComponents();
        $this->optimizeBladeComponents();
    }

    protected function discoverActions(): array
    {
        $directories = [
            flux_path('src/Actions') => 'FluxErp\Actions',
        ];

        $allActions = [];

        foreach ($directories as $directory => $namespace) {
            if (! is_dir($directory)) {
                continue;
            }

            $cacheKey = md5($directory . $namespace);
            $actions = [];

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $relativePath = ltrim(str_replace($directory, '', $file->getPath()), DIRECTORY_SEPARATOR);
                    $subNameSpace = ! empty($relativePath)
                        ? str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath) . '\\'
                        : '';
                    $class = $namespace . '\\' . $subNameSpace . $file->getBasename('.php');

                    if (
                        ! class_exists($class)
                        || ! is_a($class, \FluxErp\Actions\FluxAction::class, true)
                        || (new ReflectionClass($class))->isAbstract()
                    ) {
                        continue;
                    }

                    $actions[$class::name()] = $class;
                }
            }

            $allActions[$cacheKey] = $actions;
        }

        return $allActions;
    }

    protected function discoverWidgets(): array
    {
        $directories = [
            flux_path('src/Livewire/Widgets') => 'FluxErp\Livewire\Widgets',
        ];

        $componentRegistry = app(\Livewire\Mechanisms\ComponentRegistry::class);
        $allWidgets = [];

        foreach ($directories as $directory => $namespace) {
            if (! is_dir($directory)) {
                continue;
            }

            $cacheKey = md5($directory . $namespace);
            $widgets = [];

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory)
            );

            foreach ($iterator as $file) {
                if ($file->isDir() || $file->getExtension() !== 'php') {
                    continue;
                }

                $relativePath = str_replace($directory, '', $file->getPathname());
                $class = $namespace . str_replace(['/', '.php'], ['\\', ''], $relativePath);

                if (! class_exists($class)) {
                    continue;
                }

                $reflectionClass = new ReflectionClass($class);

                if ($reflectionClass->isAbstract()
                    || ! $reflectionClass->isSubclassOf(\Livewire\Component::class)
                ) {
                    continue;
                }

                $componentName = $componentRegistry->getName($class);
                $widgets[$componentName] = $class;
            }

            $allWidgets[$cacheKey] = $widgets;
        }

        return $allWidgets;
    }

    protected function findCommands(): array
    {
        $directories = [
            flux_path('src/Console/Commands') => 'FluxErp\Console\Commands',
        ];

        $commands = [];

        foreach ($directories as $directory => $namespace) {
            if (! is_dir($directory)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory)
            );

            foreach ($iterator as $file) {
                if ($file->isDir() || $file->getExtension() !== 'php') {
                    continue;
                }

                $relativePath = str_replace($directory, '', $file->getPathname());
                $class = $namespace . str_replace(['/', '.php'], ['\\', ''], $relativePath);

                if (! class_exists($class)) {
                    continue;
                }

                $reflectionClass = new ReflectionClass($class);

                if ($reflectionClass->isAbstract()
                    || ! $reflectionClass->isSubclassOf(Command::class)
                ) {
                    continue;
                }

                $commands[] = $class;
            }
        }

        return $commands;
    }

    protected function getViewClassAliases(): array
    {
        $livewireNamespace = 'FluxErp\\Livewire\\';
        $directoryPath = Str::replace(['\\', 'FluxErp'], ['/', flux_path()], $livewireNamespace);

        if (! is_dir($directoryPath)) {
            return [];
        }

        $components = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directoryPath)
        );

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($directoryPath, '', $file->getPathname());
            $class = $livewireNamespace . str_replace(['/', '.php'], ['\\', ''], $relativePath);

            if (! class_exists($class)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($class);

            if ($reflectionClass->isAbstract()
                || ! $reflectionClass->isSubclassOf(\Livewire\Component::class)
            ) {
                continue;
            }

            $alias = Str::of($class)
                ->after($livewireNamespace)
                ->replace(['\\', '/'], '.')
                ->kebab()
                ->prepend('flux::')
                ->toString();

            $components[$alias] = $class;
        }

        return $components;
    }

    protected function optimizeActions(): void
    {
        $cachePath = $this->laravel->bootstrapPath('cache/flux-actions.php');

        if ($this->forget) {
            if (file_exists($cachePath)) {
                unlink($cachePath);
            }

            return;
        }

        $actions = $this->discoverActions();

        $content = '<?php' . PHP_EOL . PHP_EOL;
        $content .= '// This file was auto-generated by flux:optimize' . PHP_EOL;
        $content .= 'return ' . var_export($actions, true) . ';' . PHP_EOL;

        file_put_contents($cachePath, $content);
    }

    protected function optimizeCommands(): void
    {
        $cachePath = $this->laravel->bootstrapPath('cache/flux-commands.php');

        if ($this->forget) {
            if (file_exists($cachePath)) {
                unlink($cachePath);
            }

            return;
        }

        $commands = $this->findCommands();

        $content = '<?php' . PHP_EOL . PHP_EOL;
        $content .= '// This file was auto-generated by flux:optimize' . PHP_EOL;
        $content .= 'return ' . var_export($commands, true) . ';' . PHP_EOL;

        file_put_contents($cachePath, $content);
    }

    protected function optimizeDefaultModels(): void
    {
        foreach (Relation::morphMap() as $alias => $model) {
            if (! in_array(HasDefault::class, class_uses_recursive($model))) {
                continue;
            }

            /** @var Model $model */
            $this->forget ? Cache::forget('default_' . $alias) : resolve_static($model, 'default');
        }
    }

    protected function optimizeModelInfo(): void
    {
        if ($this->forget) {
            Cache::forget(config('tall-datatables.cache_key') . '.modelInfo');

            return;
        }

        model_info_all();
    }

    protected function optimizeRepeatable(): void
    {
        $cachePath = $this->laravel->bootstrapPath('cache/flux-repeatables.php');

        if ($this->forget) {
            if (file_exists($cachePath)) {
                unlink($cachePath);
            }

            return;
        }

        $content = '<?php' . PHP_EOL . PHP_EOL;
        $content .= '// This file was auto-generated by flux:optimize' . PHP_EOL;
        $content .= 'return ' . var_export($this->discoverRepeatables(), true) . ';' . PHP_EOL;

        file_put_contents($cachePath, $content);
    }

    protected function discoverRepeatables(): array
    {
        $directories = [
            flux_path('src/Console/Commands') => 'FluxErp\\Console\\Commands',
            flux_path('src/Jobs') => 'FluxErp\\Jobs',
            flux_path('src/Invokable') => 'FluxErp\\Invokable',
            app_path('Console/Commands') => 'App\\Console\\Commands',
            app_path('Jobs') => 'App\\Jobs',
            app_path('Invokable') => 'App\\Invokable',
        ];

        $allRepeatables = [];

        foreach ($directories as $directory => $namespace) {
            if (! is_dir($directory)) {
                continue;
            }

            $cacheKey = md5($directory . $namespace);
            $repeatables = [];

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $relativePath = ltrim(
                        str_replace($directory, '', $file->getPath()),
                        DIRECTORY_SEPARATOR
                    );
                    $subNameSpace = ! empty($relativePath)
                        ? str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath) . '\\'
                        : '';
                    $class = $namespace . '\\' . $subNameSpace . $file->getBasename('.php');

                    if (! class_exists($class) || ! is_a($class, Repeatable::class, true)) {
                        continue;
                    }

                    $repeatables[$class::name()] = $class;
                }
            }

            $allRepeatables[$cacheKey] = $repeatables;
        }

        return $allRepeatables;
    }

    protected function optimizeViewClasses(): void
    {
        $cachePath = $this->laravel->bootstrapPath('cache/flux-view-classes.php');

        if ($this->forget) {
            if (file_exists($cachePath)) {
                unlink($cachePath);
            }

            return;
        }

        $content = '<?php' . PHP_EOL . PHP_EOL;
        $content .= '// This file was auto-generated by flux:optimize' . PHP_EOL;
        $content .= 'return ' . var_export($this->getViewClassAliases(), true) . ';' . PHP_EOL;

        file_put_contents($cachePath, $content);
    }

    protected function optimizeWidgets(): void
    {
        $cachePath = $this->laravel->bootstrapPath('cache/flux-widgets.php');

        if ($this->forget) {
            if (file_exists($cachePath)) {
                unlink($cachePath);
            }

            return;
        }

        $content = '<?php' . PHP_EOL . PHP_EOL;
        $content .= '// This file was auto-generated by flux:optimize' . PHP_EOL;
        $content .= 'return ' . var_export($this->discoverWidgets(), true) . ';' . PHP_EOL;

        file_put_contents($cachePath, $content);
    }

    protected function optimizeLivewireComponents(): void
    {
        $cachePath = $this->laravel->bootstrapPath('cache/flux-livewire-components.php');

        if ($this->forget) {
            if (file_exists($cachePath)) {
                unlink($cachePath);
            }

            return;
        }

        $livewireNamespace = 'FluxErp\\Livewire\\';
        $directoryPath = flux_path('src/Livewire');

        if (! is_dir($directoryPath)) {
            file_put_contents($cachePath, '<?php' . PHP_EOL . PHP_EOL . 'return [];' . PHP_EOL);

            return;
        }

        $directoryIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directoryPath));
        $phpFiles = new RegexIterator($directoryIterator, '/\.php$/');
        $components = [];

        foreach ($phpFiles as $phpFile) {
            $relativePath = ltrim(Str::replace($directoryPath, '', $phpFile->getRealPath()), '/');
            $class = $livewireNamespace . str_replace(['/', '.php'], ['\\', ''], $relativePath);

            if (class_exists($class)) {
                $exploded = explode('/', Str::remove('.php', $relativePath));
                array_walk($exploded, function (&$value): void {
                    $value = Str::snake($value, '-');
                });

                $alias = implode('.', $exploded);
                $components[$alias] = $class;
            }
        }

        $content = '<?php' . PHP_EOL . PHP_EOL;
        $content .= '// This file was auto-generated by flux:optimize' . PHP_EOL;
        $content .= 'return ' . var_export($components, true) . ';' . PHP_EOL;

        file_put_contents($cachePath, $content);
    }

    protected function optimizeBladeComponents(): void
    {
        $cachePath = $this->laravel->bootstrapPath('cache/flux-blade-components.php');

        if ($this->forget) {
            if (file_exists($cachePath)) {
                unlink($cachePath);
            }

            return;
        }

        $content = '<?php' . PHP_EOL . PHP_EOL;
        $content .= '// This file was auto-generated by flux:optimize' . PHP_EOL;
        $content .= 'return ' . var_export($this->discoverBladeComponents(), true) . ';' . PHP_EOL;

        file_put_contents($cachePath, $content);
    }

    protected function discoverBladeComponents(): array
    {
        $directoryPath = flux_path('resources/views/components');

        if (! is_dir($directoryPath)) {
            return [];
        }

        $components = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directoryPath)
        );
        $phpFiles = new RegexIterator($iterator, '/\.blade\.php$/');

        foreach ($phpFiles as $phpFile) {
            $relativePath = Str::replace($directoryPath . DIRECTORY_SEPARATOR, '', $phpFile->getRealPath());
            $relativePath = Str::replace(DIRECTORY_SEPARATOR, '.', Str::remove('.blade.php', $relativePath));

            $components[] = [
                'view' => 'flux::components.' . $relativePath,
                'alias' => Str::remove('.index', $relativePath),
            ];
        }

        return $components;
    }
}
