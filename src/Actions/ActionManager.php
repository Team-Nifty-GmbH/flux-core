<?php

namespace FluxErp\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

class ActionManager
{
    use Macroable;

    protected Collection $actions;

    protected static array $discoveries = [];

    public function __construct()
    {
        $this->actions = Collection::make();

        if (app()->bound('events')) {
            FluxAction::setEventDispatcher(app('events'));
        }
    }

    /**
     * @throws \Exception
     */
    public function register(string $name, string $action): void
    {
        if (! is_a($action, FluxAction::class, true) || $action === FluxAction::class) {
            throw new InvalidArgumentException('The provided action class is not a valid action class');
        }

        $this->actions[$name] = [
            'name' => $action::name(),
            'description' => $action::description(),
            'models' => $action::models(),
            'class' => $action,
        ];
    }

    public function all(): Collection
    {
        return $this->actions;
    }

    public function get(string $name): ?array
    {
        return $this->actions->get($name);
    }

    public function getByModel(string $model): Collection
    {
        return $this->actions->filter(fn ($item) => in_array($model, $item['models']));
    }

    public function autoDiscover(?string $directory = null, ?string $namespace = null): void
    {
        $namespace = $namespace ?: 'App\\Actions';
        $path = $directory ?: app_path('Actions');

        if (! is_dir($path)) {
            return;
        }

        $cacheKey = md5($path . $namespace);

        static::$discoveries[$cacheKey] = [
            'path' => $path,
            'namespace' => $namespace,
        ];

        try {
            $actions = Cache::get('flux.actions.' . $cacheKey);
        } catch (\Throwable) {
            $actions = null;
        }

        if (! is_null($actions) && ! app()->runningInConsole()) {
            $iterator = [];
        } else {
            $actions = [];
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
        }

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = ltrim(str_replace($path, '', $file->getPath()), DIRECTORY_SEPARATOR);
                $subNameSpace = ! empty($relativePath)
                    ? str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath) . '\\'
                    : '';
                $class = $namespace . '\\' . $subNameSpace . $file->getBasename('.php');

                if (
                    ! class_exists($class)
                    || ! is_a($class, FluxAction::class, true)
                    || (new ReflectionClass($class))->isAbstract()
                ) {
                    continue;
                }

                $actions[$class::name()] = $class;
            }
        }

        foreach ($actions as $name => $class) {
            try {
                $this->register($name, $class);
            } catch (\Exception) {
                // Ignore exceptions during auto-discovery
            }
        }

        try {
            Cache::put('flux.actions.' . $cacheKey, $actions);
        } catch (\Throwable) {
            // Ignore exceptions during cache put
        }
    }

    public function getDiscoveries(): array
    {
        return static::$discoveries;
    }
}
