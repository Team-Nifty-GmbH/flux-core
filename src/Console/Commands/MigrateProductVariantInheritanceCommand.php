<?php

namespace FluxErp\Console\Commands;

use FluxErp\Jobs\MigrateProductVariantInheritanceJob;
use FluxErp\Settings\ProductSettings;
use Illuminate\Console\Command;

/**
 * One-time bootstrap after enabling variant inheritance on an existing install:
 * materializes parent values (+ translations) and is_inherited relation rows for
 * all pre-existing variants. The live write path keeps everything in sync from
 * then on — this command is NOT needed for regular operation.
 */
class MigrateProductVariantInheritanceCommand extends Command
{
    protected $description = 'Materialize product variant inheritance for existing variants after enabling the feature.';

    protected $signature = 'flux:product-variants:migrate-inheritance {--parent-id= : Limit to a single parent product}';

    public function handle(): int
    {
        if (! app(ProductSettings::class)->variant_inheritance_enabled) {
            $this->error('Product variant inheritance is disabled (see ProductSettings).');

            return self::FAILURE;
        }

        $parentId = ! is_null($this->option('parent-id'))
            ? (int) $this->option('parent-id')
            : null;

        $this->info('Starting variant-inheritance migration job (sync) ...');
        app(MigrateProductVariantInheritanceJob::class, ['parentId' => $parentId])->handle();
        $this->info('Done. Variants processed (see logs for counts).');

        return self::SUCCESS;
    }
}
