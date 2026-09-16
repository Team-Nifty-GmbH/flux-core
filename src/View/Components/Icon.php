<?php

namespace FluxErp\View\Components;

use Closure;
use TallStackUi\Components\Icon\Component;
use TallStackUi\Support\Icons\IconGuideMap;

class Icon extends Component
{
    protected static array $rendered = [];

    protected static int $limit = 512;

    public function render(): Closure
    {
        $parent = parent::render();

        return function (array $data) use ($parent): string|object {
            if (! is_null($this->left) || ! is_null($this->right)) {
                return $parent($data);
            }

            $attributes = $this->attributes?->getAttributes() ?? [];
            $memoizable = true;

            array_walk_recursive($attributes, function (mixed $value) use (&$memoizable): void {
                $memoizable = $memoizable && (is_scalar($value) || is_null($value));
            });

            if (! $memoizable) {
                return $parent($data);
            }

            $key = json_encode([
                $this->icon ?? $this->name,
                $this->type,
                $this->internal,
                $this->error,
                $attributes,
            ]);

            if (array_key_exists($key, static::$rendered)) {
                return static::$rendered[$key];
            }

            $rendered = $parent($data);
            $rendered = is_string($rendered) ? $rendered : $rendered->render();

            if (count(static::$rendered) < static::$limit) {
                static::$rendered[$key] = $rendered;
            }

            return $rendered;
        };
    }

    public function raw(?string $path = null): string
    {
        return IconGuideMap::build($this, $path);
    }
}
