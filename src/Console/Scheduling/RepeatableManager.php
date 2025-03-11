<?php

namespace FluxErp\Console\Scheduling;

use FilesystemIterator;
use FluxErp\Enums\RepeatableTypeEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class RepeatableManager
{
    use Macroable;

    protected static array $discoveries = [];

    protected Collection $repeatable;

    public function __construct()
    {
        $this->repeatable = Collection::make();
    }

    public function all(): Collection
    {
        return $this->repeatable;
    }

    public function autoDiscover(?string $directory = null, ?string $namespace = null): void
    {
        if ($directory xor $namespace) {
            return;
        }

        if ($directory) {
            $directories = [$directory];
        } else {
            $directories = [
                app_path('Console/Commands'),
                app_path('Jobs'),
                app_path('Invokable'),
            ];
        }

        if ($namespace) {
            $namespaces = [$namespace];
        } else {
            $namespaces = [
                'App\\Console\\Commands',
                'App\\Jobs',
                'App\\Invokable',
            ];
        }

        foreach ($directories as $key => $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            $cacheKey = md5($directory . implode($namespaces));

            static::$discoveries[$cacheKey] = [
                'path' => $directory,
                'namespaces' => $namespaces,
            ];

            // try to obtain the repeatables from cache
            // if the cache is not available, we will iterate over the directory
            try {
                $repeatables = Cache::get('flux.repeatable.' . $cacheKey);
            } catch (\Throwable) {
                $repeatables = null;
            }

            if (! is_null($repeatables) && ! app()->runningInConsole()) {
                $iterator = [];
            } else {
                $repeatables = [];
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
            }

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $relativePath = ltrim(
                        str_replace($directory, '', $file->getPath()),
                        DIRECTORY_SEPARATOR
                    );
                    $subNameSpace = ! empty($relativePath)
                        ? str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath) . '\\'
                        : '';
                    $class = $namespaces[$key] . '\\' . $subNameSpace . $file->getBasename('.php');

                    if (! class_exists($class) || ! is_a($class, Repeatable::class, true)) {
                        continue;
                    }

                    $repeatables[$class::name()] = $class;
                }
            }

            foreach ($repeatables as $name => $class) {
                try {
                    $this->register($name, $class);
                } catch (InvalidArgumentException) {
                    // Ignore exceptions during auto-discovery
                }
            }

            try {
                Cache::put('flux.repeatable.' . $cacheKey, $repeatables);
            } catch (\Throwable) {
                // Ignore exceptions during cache put
            }
        }
    }

    public function get(string $name): ?array
    {
        return $this->repeatable->get($name);
    }

    public function getDiscoveries(): array
    {
        return static::$discoveries;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function register(string $name, string $class): void
    {
        if (! is_a($class, Repeatable::class, true)) {
            throw new InvalidArgumentException('The provided class is not repeatable');
        }

        // Valid repeatable classes are artisan commands, jobs and invokable classes.
        $type = match (true) {
            method_exists($class, 'repeatableType')
                && $class::repeatableType() instanceof RepeatableTypeEnum => $class::repeatableType(),
            is_a($class, Command::class, true) => RepeatableTypeEnum::Command,
            $this->isJob($class) => RepeatableTypeEnum::Job,
            method_exists($class, '__invoke') => RepeatableTypeEnum::Invokable,
            default => throw new InvalidArgumentException(
                'The provided class is not a artisan command nor a job nor an invokable class'
            ),
        };

        $this->repeatable[$name] = [
            'name' => $class::name(),
            'description' => $class::description(),
            'class' => $class,
            'type' => $type,
            'parameters' => $class::parameters(),
        ];
    }

    private function isJob(string $class): bool
    {
        return is_a($class, ShouldQueue::class, true)
            && ! array_diff(
                [
                    Dispatchable::class,
                    InteractsWithQueue::class,
                    Queueable::class,
                ],
                class_uses_recursive($class)
            );
    }
}
