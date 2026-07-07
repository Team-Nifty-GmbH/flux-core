<?php

namespace FluxErp\Support\VariantInheritance;

use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Materializes parent -> child propagation for Product's inheritable pivot relations
 * (categories, suppliers, productProperties) as is_inherited=true pivot rows.
 *
 * Pivot sync()/attach() does not reliably fire per-row pivot model events, so this can't
 * hook into pivot model booted() the way Price does. Instead it must be invoked explicitly
 * from the action choke point (UpdateProduct::performAction) right after the parent's own
 * pivots are synced.
 */
class PivotInheritanceSync
{
    protected const RELATIONS = ['categories', 'suppliers', 'productProperties'];

    public static function propagateToChildren(Product $parent): void
    {
        if (! is_null($parent->parent_id) || ! $parent->inheritanceEnabled()) {
            return;
        }

        $childIds = $parent->children()->pluck('id');

        if ($childIds->isEmpty()) {
            return;
        }

        foreach (self::RELATIONS as $relation) {
            if ($parent->isInheritableRelation($relation)) {
                static::propagateRelation($parent, $relation, $childIds);
            }
        }
    }

    protected static function propagateRelation(Product $parent, string $relation, Collection $childIds): void
    {
        $rel = $parent->{'own' . ucfirst($relation)}();

        if (! $rel instanceof BelongsToMany) {
            throw new LogicException("Unsupported pivot relation type for propagation: [$relation].");
        }

        $pivotTable = $rel->getTable();
        $foreignPivotKey = $rel->getForeignPivotKeyName();
        $relatedPivotKey = $rel->getRelatedPivotKeyName();
        $extraColumns = $rel->getPivotColumns();
        $isMorph = $rel instanceof MorphToMany;
        $morphType = $isMorph ? $rel->getMorphType() : null;
        $morphClass = $isMorph ? $rel->getMorphClass() : null;

        $scoped = fn () => DB::table($pivotTable)
            ->when($isMorph, fn ($query) => $query->where($morphType, $morphClass));

        // The parent only ever owns rows (it has no parent of its own to inherit from).
        $parentRows = $scoped()
            ->where($foreignPivotKey, $parent->getKey())
            ->get(array_merge([$relatedPivotKey], $extraColumns))
            ->keyBy($relatedPivotKey);

        $parentKeys = $parentRows->keys();

        // Drop inherited child copies for related keys the parent no longer owns.
        $scoped()
            ->whereIn($foreignPivotKey, $childIds)
            ->where('is_inherited', true)
            ->when(
                $parentKeys->isNotEmpty(),
                fn ($query) => $query->whereNotIn($relatedPivotKey, $parentKeys),
            )
            ->delete();

        if ($parentKeys->isEmpty()) {
            return;
        }

        $ownedByKey = $scoped()
            ->whereIn($foreignPivotKey, $childIds)
            ->where('is_inherited', false)
            ->whereIn($relatedPivotKey, $parentKeys)
            ->get([$foreignPivotKey, $relatedPivotKey])
            ->groupBy($relatedPivotKey)
            ->map(fn ($rows) => $rows->pluck($foreignPivotKey));

        $existingByKey = $scoped()
            ->whereIn($foreignPivotKey, $childIds)
            ->where('is_inherited', true)
            ->whereIn($relatedPivotKey, $parentKeys)
            ->get([$foreignPivotKey, $relatedPivotKey])
            ->groupBy($relatedPivotKey)
            ->map(fn ($rows) => $rows->pluck($foreignPivotKey));

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
                    ->whereIn($foreignPivotKey, $toUpdateIds)
                    ->where($relatedPivotKey, $key)
                    ->where('is_inherited', true)
                    ->update(collect($extraColumns)
                        ->mapWithKeys(fn ($column) => [$column => $parentRow->{$column}])
                        ->all());
            }

            if ($toInsertIds->isNotEmpty()) {
                DB::table($pivotTable)->insert(
                    $toInsertIds->map(function ($childId) use (
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
                    })->all()
                );
            }
        }
    }
}
