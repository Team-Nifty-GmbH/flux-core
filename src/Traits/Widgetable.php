<?php

namespace FluxErp\Traits;

use Illuminate\Support\Str;

trait Widgetable
{
    public static function getLabel(): string
    {
        return __(Str::headline(class_basename(static::class)));
    }
}
