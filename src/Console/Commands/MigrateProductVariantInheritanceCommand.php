<?php

namespace FluxErp\Console\Commands;

use Cron\CronExpression;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Jobs\MigrateProductVariantInheritanceJob;
use FluxErp\Settings\ProductSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MigrateProductVariantInheritanceCommand extends Command implements Repeatable
{
    protected $description = 'Reconcile/materialize product variant inheritance: copies parent values to non-overriding children.';

    protected $signature = 'flux:product-variants:migrate-inheritance {--parent-id= : Limit to a single parent product}';

    public static function defaultCron(): ?CronExpression
    {
        return new CronExpression('0 0 * * *');
    }

    public static function description(): ?string
    {
        return 'Nightly repair: materialize product variant inheritance for all parents.';
    }

    public static function isRepeatable(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return Str::headline(class_basename(static::class));
    }

    public static function parameters(): array
    {
        return [];
    }

    public static function withoutOverlapping(): bool
    {
        return true;
    }

    public function handle(): int
    {
        if (! app(ProductSettings::class)->variant_inheritance_enabled) {
            $this->error('Product variant inheritance is disabled (see ProductSettings).');

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
