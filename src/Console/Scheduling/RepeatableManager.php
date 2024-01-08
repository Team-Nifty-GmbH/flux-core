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
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class RepeatableManager
{
    use Macroable;

    protected Collection $repeatable;

    public function __construct()
    {
        $this->repeatable = Collection::make();
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

    public function all(): Collection
    {
        return $this->repeatable;
    }

    public function get(string $name): ?array
    {
        return $this->repeatable->get($name);
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
                app_path('Repeatable'),
            ];
        }

        if ($namespace) {
            $namespaces = [$namespace];
        } else {
            $namespaces = [
                'App\\Console\\Commands',
                'App\\Jobs',
                'App\\Repeatable',
            ];
        }

        foreach ($directories as $key => $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
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
                    $class = $namespaces[$key] . '\\' . $subNameSpace . $file->getBasename('.php');

                    if (! class_exists($class) || ! is_a($class, Repeatable::class, true)) {
                        continue;
                    }

                    try {
                        $this->register($class::name(), $class);
                    } catch (InvalidArgumentException) {
                        // Ignore exceptions during auto-discovery
                    }
                }
            }
        }
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