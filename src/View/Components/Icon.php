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

            $key = ($this->icon ?? $this->name) . '|' . ($this->type ?? '') . '|' . ($this->internal ? 1 : 0)
                . '|' . ($this->error ? 1 : 0) . '|' . ($this->attributes?->toHtml() ?? '');

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
