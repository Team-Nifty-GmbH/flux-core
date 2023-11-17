<?php

namespace FluxErp\Actions;

use FilesystemIterator;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ActionManager
{
    use Macroable;

    protected Collection $actions;

    public function __construct()
    {
        $this->actions = Collection::make();
    }

    /**
     * @throws \Exception
     */
    public function register(string $name, string $action): void
    {
        if (! in_array(FluxAction::class, class_parents($action))) {
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

    public function autoDiscover(string $directory = null, string $namespace = null): void
    {
        $namespace = $namespace ?: 'App\\Actions';
        $path = $directory ?: app_path('Actions');

        if (! is_dir($path)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = ltrim(str_replace($path, '', $file->getPath()), DIRECTORY_SEPARATOR);
                $subNameSpace = ! empty($relativePath)
                    ? str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath) . '\\'
                    : '';
                $class = $namespace . '\\' . $subNameSpace . $file->getBasename('.php');

                if (! class_exists($class) || ! in_array(FluxAction::class, class_parents($class))) {
                    continue;
                }

                try {
                    $this->register($class::name(), $class);
                } catch (\Exception) {
                    // Ignore exceptions during auto-discovery
                }
            }
        }
    }
}
