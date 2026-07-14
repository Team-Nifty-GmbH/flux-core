<?php

namespace FluxErp\Settings;

class ProductSettings extends FluxSettings
{
    public bool $variant_inheritance_enabled = true;

    public static function group(): string
    {
        return 'product';
    }
}
