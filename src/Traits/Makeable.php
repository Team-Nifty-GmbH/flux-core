<?php

namespace FluxErp\Traits;

trait Makeable
{
    public static function make(...$data): static
    {
        return new static($data);
    }
}
