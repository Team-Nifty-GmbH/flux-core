<?php

namespace FluxErp\Support\Container;

use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;

class ProductTypeManager
{
    use Macroable;

    protected Collection $productTypes;

    public function __construct()
    {
        $this->productTypes = collect();
    }

    /**
     * @throws \Exception
     */
    public function register(string $name, string $view, bool $default = false): void
    {
        if (! view()->exists($view)) {
            throw new InvalidArgumentException('The provided view does not exist.');
        }

        if ($default
            && ($key = $this->productTypes->search(fn ($type) => $type['is_default'])) !== false
        ) {
            $this->productTypes[$key]['is_default'] = false;
        }

        $this->productTypes[$name] = [
            'type' => $name,
            'view' => $view,
            'is_default' => $default,
        ];
    }

    public function unregister(string $name): void
    {
        $this->productTypes->forget($name);
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
}
