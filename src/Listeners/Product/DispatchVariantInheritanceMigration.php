<?php

namespace FluxErp\Listeners\Product;

use FluxErp\Jobs\MigrateProductVariantInheritanceJob;
use FluxErp\Settings\ProductSettings;
use Spatie\LaravelSettings\Events\SavingSettings;

/**
 * Bootstraps variant inheritance when the feature is switched on: existing
 * variants were created without materialized values, so enabling the setting
 * dispatches the one-time migration that copies parent values, translations
 * and is_inherited relation rows onto them. The live write path keeps
 * everything in sync from then on.
 */
class DispatchVariantInheritanceMigration
{
    public function handle(SavingSettings $event): void
    {
        if (! $event->settings instanceof ProductSettings) {
            return;
        }

        $wasEnabled = (bool) data_get($event->originalValues, 'variant_inheritance_enabled', false);
        $isEnabled = (bool) $event->properties->get('variant_inheritance_enabled');

        if (! $wasEnabled && $isEnabled) {
            MigrateProductVariantInheritanceJob::dispatch()
                ->afterCommit();
        }
    }
}
