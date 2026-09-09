<?php

namespace FluxErp\Console\Commands;

use FluxErp\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class RecordVariantOverridesCommand extends Command
{
    protected $description = 'Record which inheritable fields a variant already carries itself, so inheritance leaves them alone.';

    protected $signature = 'flux:product-variants:record-overrides
            {--dry-run : Only report what would be recorded}';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $fields = app(Product::class)->getInheritableFields();
        $columns = array_merge(['id', 'parent_id', 'overridden_fields'], $fields);

        $variants = 0;
        $recorded = 0;

        resolve_static(Product::class, 'query')
            ->whereNotNull('parent_id')
            ->with(['parent' => fn ($query) => $query->select(array_merge(['id'], $fields))])
            ->select($columns)
            ->chunkById(500, function (Collection $chunk) use ($fields, $dryRun, &$variants, &$recorded): void {
                foreach ($chunk as $variant) {
                    $variants++;

                    if (! $variant->parent) {
                        continue;
                    }

                    $known = $variant->overridden_fields ?? [];
                    $found = $this->differingFields($variant, $fields);
                    $merged = array_values(array_unique(array_merge($known, $found)));

                    if (count($merged) === count($known)) {
                        continue;
                    }

                    $recorded++;

                    if ($dryRun) {
                        continue;
                    }

                    resolve_static(Product::class, 'query')
                        ->whereKey($variant->getKey())
                        ->update(['overridden_fields' => $merged]);
                }
            });

        $this->info(
            $dryRun
                ? "{$recorded} of {$variants} variants carry values of their own that are not recorded yet."
                : "Recorded own values on {$recorded} of {$variants} variants."
        );

        return self::SUCCESS;
    }

    protected function differingFields(Product $variant, array $fields): array
    {
        $differing = [];

        foreach ($fields as $field) {
            if ($variant->getRawOriginal($field) !== $variant->parent->getRawOriginal($field)) {
                $differing[] = $field;
            }
        }

        return $differing;
    }
}
