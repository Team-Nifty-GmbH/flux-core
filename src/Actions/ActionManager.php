<?php

namespace FluxErp\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;

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
        if (! in_array(BaseAction::class, class_parents($action))) {
            throw new InvalidArgumentException('The provided action class is not a valid action class');
        }

        $this->actions[$name] = [
            'name' => $action::name(),
            'description' => $action::description(),
            'models' => $action::models(),
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

        foreach (glob("{$path}/**/*.php") as $file) {
            $subNameSpace = str_replace([$directory, '/'], ['', '\\'], pathinfo($file, PATHINFO_DIRNAME)) . '\\';
            $class = $namespace . $subNameSpace . pathinfo($file, PATHINFO_FILENAME);

            if (! class_exists($class) || ! in_array(BaseAction::class, class_parents($class))) {
                continue;
            }

            try {
                $this->register($class::name(), $class);
            } catch (\Exception) {
            }
        }
    }
}
