<?php

namespace FluxErp\Settings;

use Spatie\LaravelSettings\Exceptions\MissingSettings;

class ProductSettings extends FluxSettings
{
    public bool $variant_inheritance_enabled;

    public static function group(): string
    {
        return 'product';
    }

    /**
     * Resilient gate for the variant inheritance feature. Falls back to the
     * create_product_settings migration default (true) when the setting has
     * not been migrated yet, so product writes never crash on an app (or a
     * schema-dumped test database) where the row is absent.
     */
    public static function variantInheritanceEnabled(): bool
    {
        try {
            return (bool) app(static::class)->variant_inheritance_enabled;
        } catch (MissingSettings) {
            return true;
        }
    }
}
