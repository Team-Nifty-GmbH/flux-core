<?php

namespace FluxErp\Jobs;

use FluxErp\Actions\Product\SyncVariantInheritance;
use FluxErp\Models\Product;
use FluxErp\Settings\ProductSettings;
use FluxErp\Support\VariantInheritance\PivotInheritanceSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * One-time backfill: materializes existing variants under the new (Approach B) model.
 *
 * For every parent product, copies the parent's current inheritable field values (+
 * translations) into non-overriding child columns and seeds `is_inherited = true`
 * relation rows (prices + categories/suppliers/productProperties) for non-owning
 * children. Reuses the same propagation helpers the live write path uses, so this is
 * idempotent — running it again is a no-op for already-materialized data.
 */
class MigrateProductVariantInheritanceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $batchSize = 500;

    public function __construct(public ?int $parentId = null) {}

    public function handle(): void
    {
        if (! app(ProductSettings::class)->variant_inheritance_enabled) {
            return;
        }

        $query = resolve_static(Product::class, 'query')
            ->whereNull('parent_id')
            ->has('children')
            ->with('ownPrices');

        if ($this->parentId !== null) {
            $query->whereKey($this->parentId);
        }

        $processed = 0;

        $query->chunkById($this->batchSize, function ($parents) use (&$processed): void {
            foreach ($parents as $parent) {
                $this->materialize($parent);
                $processed++;
            }
        });

        Log::info('MigrateProductVariantInheritanceJob materialized parents', [
            'parent_id' => $this->parentId,
            'processed' => $processed,
        ]);
    }

    protected function materialize(Product $parent): void
    {
        // Copies parent field values (+ translations) into non-overriding children and
        // reindexes them in Scout.
        SyncVariantInheritance::make(['parent_id' => $parent->getKey()])
            ->validate()
            ->execute();

        // Seeds/refreshes is_inherited=true pivot rows for non-owning children.
        resolve_static(PivotInheritanceSync::class, 'propagateToChildren', ['parent' => $parent]);

        // Re-saving the parent's own prices retriggers Price::booted()'s saved-event
        // propagation, which materializes missing/stale inherited child price rows.
        $parent->ownPrices->each->save();
    }
}
