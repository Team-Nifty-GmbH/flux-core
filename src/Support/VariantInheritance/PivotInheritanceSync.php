<?php

namespace FluxErp\Support\VariantInheritance;

use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Materializes parent -> child propagation for the product's inheritable pivot
 * relations as is_inherited=true pivot rows.
 *
 * Pivot sync()/attach() does not reliably fire per-row pivot model events, so this can't
 * hook into pivot model booted() the way Price does. Instead it must be invoked explicitly
 * from the action choke point (UpdateProduct::performAction) right after the parent's own
 * pivots are synced.
 */
class PivotInheritanceSync
{
    /**
     * Sync a product's "own" pivot relation (categories/suppliers/productProperties),
     * keyed by the related id => extra pivot attributes.
     *
     * A non-variant (or non-inheriting) product keeps the plain sync() behavior: the
     * payload is the full desired set, anything absent gets detached.
     *
     * A variant taking ownership must not let payload omission delete its inherited
     * copies — those are materialized by parent propagation and removed only by the
     * reset actions, never by omission. So only rows the variant already owns and no
     * longer lists get detached; every listed entry becomes owned (is_inherited =
     * false), flipping an existing inherited copy in place instead of duplicating it.
     */
    public static function syncOwned(BelongsToMany $relation, array $desired, bool $takesOwnership): void
    {
        if (! $takesOwnership) {
            $relation->sync($desired);

            return;
        }

        $relation->sync(
            collect($desired)
                ->map(fn (array $attributes) => array_merge($attributes, ['is_inherited' => false]))
                ->all(),
            false
        );

        $relation->newPivotQuery()
            ->where('is_inherited', false)
            ->whereNotIn($relation->getRelatedPivotKeyName(), array_keys($desired))
            ->delete();
    }

    public static function propagateToChildren(Product $parent): void
    {
        if (! is_null($parent->parent_id) || ! $parent->inheritanceEnabled()) {
            return;
        }

        $childIds = $parent->children()->pluck('id');

        if ($childIds->isEmpty()) {
            return;
        }

        // Every inheritable pivot relation (prices are HasMany and propagate through
        // the saved()/deleted() hooks on the price model instead).
        foreach ($parent->getInheritableRelations() as $relation) {
            if ($parent->{'own' . ucfirst($relation)}() instanceof BelongsToMany) {
                static::propagateRelation($parent, $relation, $childIds);
            }
        }
    }

    protected static function propagateRelation(Product $parent, string $relation, Collection $childIds): void
    {
        $relationInstance = $parent->{'own' . ucfirst($relation)}();

        $pivotTable = $relationInstance->getTable();
        $foreignPivotKey = $relationInstance->getForeignPivotKeyName();
        $relatedPivotKey = $relationInstance->getRelatedPivotKeyName();
        $extraColumns = $relationInstance->getPivotColumns();
        $isMorph = $relationInstance instanceof MorphToMany;
        $morphType = $isMorph ? $relationInstance->getMorphType() : null;
        $morphClass = $isMorph ? $relationInstance->getMorphClass() : null;

        $scoped = fn () => DB::table($pivotTable)
            ->when($isMorph, fn (QueryBuilder $query) => $query->where($morphType, $morphClass));

        // The parent only ever owns rows (it has no parent of its own to inherit from).
        $parentRows = $scoped()
            ->where($foreignPivotKey, $parent->getKey())
            ->get(array_merge([$relatedPivotKey], $extraColumns))
            ->keyBy($relatedPivotKey);

        $parentKeys = $parentRows->keys();

        // Drop inherited child copies for related keys the parent no longer owns.
        $scoped()
            ->whereIntegerInRaw($foreignPivotKey, $childIds)
            ->where('is_inherited', true)
            ->when(
                $parentKeys->isNotEmpty(),
                fn (QueryBuilder $query) => $query->whereIntegerNotInRaw($relatedPivotKey, $parentKeys),
            )
            ->delete();

        if ($parentKeys->isEmpty()) {
            return;
        }

        $ownedByKey = $scoped()
            ->whereIntegerInRaw($foreignPivotKey, $childIds)
            ->whereIntegerInRaw($relatedPivotKey, $parentKeys)
            ->where('is_inherited', false)
            ->get([$foreignPivotKey, $relatedPivotKey])
            ->groupBy($relatedPivotKey)
            ->map(fn (Collection $rows) => $rows->pluck($foreignPivotKey));

        $existingByKey = $scoped()
            ->whereIntegerInRaw($foreignPivotKey, $childIds)
            ->whereIntegerInRaw($relatedPivotKey, $parentKeys)
            ->where('is_inherited', true)
            ->get([$foreignPivotKey, $relatedPivotKey])
            ->groupBy($relatedPivotKey)
            ->map(fn (Collection $rows) => $rows->pluck($foreignPivotKey));

        foreach ($parentRows as $key => $parentRow) {
            // Children that own their own entry for this key must never be touched.
            $targetChildIds = $childIds->diff($ownedByKey->get($key, collect()));

            if ($targetChildIds->isEmpty()) {
                continue;
            }

            $existingChildIds = $existingByKey->get($key, collect());
            $toUpdateIds = $targetChildIds->intersect($existingChildIds);
            $toInsertIds = $targetChildIds->diff($existingChildIds);

            if ($extraColumns && $toUpdateIds->isNotEmpty()) {
                $scoped()
                    ->whereIntegerInRaw($foreignPivotKey, $toUpdateIds)
                    ->where($relatedPivotKey, $key)
                    ->where('is_inherited', true)
                    ->update(collect($extraColumns)
                        ->mapWithKeys(fn (string $column) => [$column => $parentRow->{$column}])
                        ->all());
            }

            if ($toInsertIds->isNotEmpty()) {
                DB::table($pivotTable)->insert(
                    $toInsertIds
                        ->map(function ($childId) use (
                            $foreignPivotKey,
                            $relatedPivotKey,
                            $key,
                            $extraColumns,
                            $parentRow,
                            $isMorph,
                            $morphType,
                            $morphClass
                        ) {
                            $row = [
                                $foreignPivotKey => $childId,
                                $relatedPivotKey => $key,
                                'is_inherited' => true,
                            ];

                            foreach ($extraColumns as $column) {
                                $row[$column] = $parentRow->{$column};
                            }

                            if ($isMorph) {
                                $row[$morphType] = $morphClass;
                            }

                            return $row;
                        })
                        ->all()
                );
            }
        }
    }
}
