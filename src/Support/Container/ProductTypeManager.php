<?php

namespace FluxErp\Support\Container;

use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use Livewire\Component;

class ProductTypeManager
{
    use Macroable;

    protected Collection $productTypes;

    public function __construct()
    {
        $this->productTypes = collect();
    }

    public function all(): Collection
    {
        return $this->productTypes;
    }

    public function get(?string $name): ?array
    {
        return $this->productTypes->get($name);
    }

    public function getDefault(): ?array
    {
        return $this->productTypes->first(fn ($type) => $type['is_default']);
    }

    /**
     * @throws \Exception
     */
    public function register(string $name, ?string $class = null, ?string $view = null, bool $default = false): void
    {
        if (! $class && ! $view) {
            throw new InvalidArgumentException('Either a class or a view must be provided.');
        }

        if ($class
            && (
                ! is_a($class, Component::class, true)
                || (new \ReflectionClass($class))->isAbstract()
            )
        ) {
            throw new InvalidArgumentException('The provided class must be a non-abstract livewire component.');
        }

        if ($view && ! view()->exists($view)) {
            throw new InvalidArgumentException('The provided view does not exist.');
        }

        if ($default
            && ($key = $this->productTypes->search(fn ($type) => $type['is_default'])) !== false
        ) {
            $this->productTypes->put($key, array_merge($this->productTypes->get($key), ['is_default' => false]));
        }

        $this->productTypes[$name] = [
            'type' => $name,
            'class' => $class ?: null,
            'view' => $view ?: null,
            'is_default' => $default,
        ];
    }

    public function unregister(string $name): void
    {
        $this->productTypes->forget($name);
    }
}
