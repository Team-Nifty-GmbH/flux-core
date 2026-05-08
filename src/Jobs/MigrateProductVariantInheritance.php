<?php

namespace FluxErp\Jobs;

use FluxErp\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MigrateProductVariantInheritance implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $batchSize = 500;

    public function __construct(public ?int $parentId = null) {}

    public function handle(): void
    {
        $query = resolve_static(Product::class, 'query')
            ->whereNotNull('parent_id')
            ->with('parent');

        if ($this->parentId !== null) {
            $query->where('parent_id', $this->parentId);
        }

        $processed = 0;

        $query->chunkById($this->batchSize, function ($variants) use (&$processed): void {
            foreach ($variants as $variant) {
                $this->reconcileFields($variant);
                $this->reconcilePivotRelations($variant);
                $processed++;
            }
        });

        Log::info('MigrateProductVariantInheritance processed variants', [
            'parent_id' => $this->parentId,
            'processed' => $processed,
        ]);
    }

    protected function reconcileFields(Product $variant): void
    {
        if (! $variant->parent) {
            return;
        }

        $overridden = [];

        foreach ($variant->getInheritableFields() as $field) {
            $variantRaw = $variant->getAttributes()[$field] ?? null;
            $parentRaw = $variant->parent->getAttributes()[$field] ?? null;

            if ($variantRaw !== $parentRaw) {
                $overridden[] = $field;
            }
        }

        $variant->setRawAttributes(array_merge(
            $variant->getAttributes(),
            ['overridden_fields' => $overridden ? json_encode($overridden) : null]
        ), sync: false);

        $variant->saveQuietly();
    }

    protected function reconcilePivotRelations(Product $variant): void
    {
        $parent = $variant->parent;
        if (! $parent) {
            return;
        }

        $this->reconcilePrices($variant, $parent);
        $this->reconcileCategories($variant, $parent);
        $this->reconcileProductProperties($variant, $parent);
        $this->reconcileSuppliers($variant, $parent);
    }

    protected function reconcilePrices(Product $variant, Product $parent): void
    {
        $parentPriceIndex = $parent->ownPrices->keyBy('price_list_id');

        foreach ($variant->ownPrices as $variantPrice) {
            $parentPrice = $parentPriceIndex->get($variantPrice->price_list_id);
            if ($parentPrice && (string) $parentPrice->price === (string) $variantPrice->price) {
                $variantPrice->delete();
            }
        }
    }

    protected function reconcileCategories(Product $variant, Product $parent): void
    {
        $parentCategoryIds = $parent->ownCategories()->pluck('categories.id')->all();

        if (empty($parentCategoryIds)) {
            return;
        }

        $variant->ownCategories()->detach($parentCategoryIds);
    }

    protected function reconcileProductProperties(Product $variant, Product $parent): void
    {
        $parentProps = $parent->ownProductProperties()->get()->keyBy('id');

        foreach ($variant->ownProductProperties()->get() as $vp) {
            $pp = $parentProps->get($vp->getKey());
            if ($pp && (string) $pp->pivot->value === (string) $vp->pivot->value) {
                $variant->ownProductProperties()->detach($vp->getKey());
            }
        }
    }

    protected function reconcileSuppliers(Product $variant, Product $parent): void
    {
        $parentSupplierIds = $parent->ownSuppliers()->pluck('contacts.id')->all();

        if (empty($parentSupplierIds)) {
            return;
        }

        $variant->ownSuppliers()->detach($parentSupplierIds);
    }
}
