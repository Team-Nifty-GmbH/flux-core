<?php

namespace FluxErp\Traits;

trait Makeable
{
    public static function make(mixed ...$arguments): static
    {
        $class = resolve_static(static::class, 'class');

        return new $class(...$arguments);
    }
}
