<?php

namespace FluxErp\Settings;

use Illuminate\Support\Str;
use Spatie\LaravelSettings\Settings;

abstract class FluxSetting extends Settings
{
    abstract public static function componentClass(): string;

    public static function label(): string
    {
        return Str::of(class_basename(static::class))
            ->headline()
            ->toString();
    }
}
