<?php

namespace FluxErp\Console\Commands;

use FluxErp\Jobs\MigrateProductVariantInheritanceJob;
use FluxErp\Models\Tenant;
use Illuminate\Console\Command;

class MigrateProductVariantInheritanceCommand extends Command
{
    protected $description = 'Auto-diff existing product variants vs their parents and write overridden_fields.';

    protected $signature = 'flux:product-variants:migrate-inheritance {--parent-id= : Limit to a single parent product}';

    public function handle(): int
    {
        $tenant = resolve_static(Tenant::class, 'default');

        if (! $tenant?->product_variant_inheritance_enabled) {
            $this->error('Product variant inheritance is disabled for the default tenant.');

            return self::FAILURE;
        }

        $parentId = $this->option('parent-id') !== null
            ? (int) $this->option('parent-id')
            : null;

        $this->info('Starting variant-inheritance migration job (sync) ...');
        (new MigrateProductVariantInheritanceJob($parentId))->handle();
        $this->info('Done. Variants processed (see logs for counts).');

        return self::SUCCESS;
    }
}
