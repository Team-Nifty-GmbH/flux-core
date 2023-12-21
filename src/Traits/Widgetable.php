<?php

namespace FluxErp\Traits;

use Illuminate\Support\Str;

trait Widgetable
{
    public static function getLabel(): string
    {
        if (app()->runningInConsole()) {
            return Str::headline(class_basename(static::class));
        }

        return __(Str::headline(class_basename(static::class)));
    }
}
