<?php

namespace FluxErp\Settings;

use Spatie\LaravelSettings\Settings;

abstract class FluxSettings extends Settings
{
    public static function rules(): array
    {
        return [];
    }
}
