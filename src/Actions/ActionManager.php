<?php

namespace FluxErp\Actions;

use FluxErp\Contracts\ActionInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;

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
        if (! in_array(ActionInterface::class, class_implements($action))) {
            throw new \Exception('The provided action class is not a valid action class');
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

    public function get(string $name): array|null
    {
        return $this->actions->get($name);
    }

    public function getByModel(string $model): Collection
    {
        return $this->actions->filter(fn ($item) => in_array($model, $item['models']));
    }

    public function autoDiscover(string|null $directory = null, string|null $namespace = null): void
    {
        $namespace = $namespace ?: 'App\\Actions';
        $path = $directory ?: app_path('Actions');

        foreach (glob("{$path}/*.php") as $file) {
            $class = $namespace . '\\' . pathinfo($file, PATHINFO_FILENAME);

            if (! class_exists($class) || ! in_array(ActionInterface::class, class_implements($class))) {
                continue;
            }

            try {
                $this->register($class::name(), $class);
            } catch (\Exception)
            {
            }
        }
    }
}
