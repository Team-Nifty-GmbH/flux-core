<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\ResetProductFieldsRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ResetProductFields extends FluxAction
{
    public static function models(): array
    {
        return [Product::class];
    }

    protected function getRulesets(): string|array
    {
        return ResetProductFieldsRuleset::class;
    }

    public function performAction(): int
    {
        $parent = resolve_static(Product::class, 'query')
            ->whereKey($this->getData('parent_id'))
            ->firstOrFail();

        $fields = $this->getData('fields');
        $variantIds = $this->getData('variant_ids');

        $variants = $parent->children()
            ->when(
                $variantIds,
                fn (Builder $query, array $ids) => $query->whereIntegerInRaw('id', $ids)
            )
            ->where(function (Builder $query) use ($fields): void {
                foreach ($fields as $field) {
                    $query->orWhereJsonContains('overridden_fields', $field);
                }
            })
            ->get(['id', 'overridden_fields']);

        if ($variants->isNotEmpty()) {
            // Group by the resulting shape so we issue one UPDATE per distinct JSON value.
            $groups = $variants->groupBy(function (Product $variant) use ($fields): string {
                $remaining = array_values(array_diff($variant->overridden_fields ?? [], $fields));

                return $remaining === [] ? '__null__' : json_encode($remaining);
            });

            DB::transaction(function () use ($groups): void {
                foreach ($groups as $shape => $rows) {
                    resolve_static(Product::class, 'query')
                        ->whereKey($rows->pluck('id')->all())
                        ->update([
                            'overridden_fields' => $shape === '__null__' ? null : json_decode($shape, true),
                        ]);
                }
            });

            // Re-copy the parent's current values (and translations) onto the now
            // un-overridden variants so the materialized columns don't go stale.
            SyncVariantInheritance::make([
                'parent_id' => $parent->getKey(),
                'fields' => $fields,
                'variant_ids' => $variantIds,
            ])
                ->validate()
                ->execute();
        }

        return $variants->count();
    }
}
