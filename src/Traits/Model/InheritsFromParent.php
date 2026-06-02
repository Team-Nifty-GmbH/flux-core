<?php

namespace FluxErp\Traits\Model;

use FluxErp\Settings\ProductSettings;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

/**
 * Adds parent → child field/relation inheritance to a model that also uses
 * HasParentChildRelations (provides getParentKeyAttribute()).
 *
 * The consuming class must declare:
 *   protected array $inheritableFields = [...];     // column names
 *   protected array $inheritableRelations = [...];  // relation method names
 *
 * Inheritance is gated install-wide via the `product.variant_inheritance_enabled`
 * setting. Disable it in `ProductSettings` to bypass parent fallback entirely.
 *
 * @property-read array<int, string> $inheritableFields
 * @property-read array<int, string> $inheritableRelations
 */
trait InheritsFromParent
{
    public function isVariant(): bool
    {
        return ! is_null($this->{$this->getParentKeyAttribute()});
    }

    public function inheritanceEnabled(): bool
    {
        return (bool) app(ProductSettings::class)->variant_inheritance_enabled;
    }

    public function isInheritableField(string $field): bool
    {
        return in_array($field, $this->inheritableFields, strict: true);
    }

    public function isInheritableRelation(string $relation): bool
    {
        return in_array($relation, $this->inheritableRelations, strict: true);
    }

    public function getInheritableFields(): array
    {
        return $this->inheritableFields;
    }

    public function getInheritableRelations(): array
    {
        return $this->inheritableRelations;
    }

    public function overrides(string $field): bool
    {
        $list = $this->overridden_fields ?? [];

        return in_array($field, $list, strict: true);
    }

    public function getAttribute($key)
    {
        if (
            $this->isInheritableField($key)
            && $this->isVariant()
            && ! $this->overrides($key)
            && $this->inheritanceEnabled()
        ) {
            $parent = $this->relationLoaded('parent') ? $this->parent : $this->parent()->first();

            if ($parent) {
                return $parent->getAttribute($key);
            }
        }

        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        if (
            $this->isInheritableField($key)
            && $this->isVariant()
            && $this->inheritanceEnabled()
        ) {
            $list = $this->overridden_fields ?? [];

            if (! in_array($key, $list, strict: true)) {
                $list[] = $key;
                parent::setAttribute('overridden_fields', $list);
            }
        }

        return parent::setAttribute($key, $value);
    }

    public function resetField(string $field): static
    {
        if (! $this->isInheritableField($field)) {
            throw new InvalidArgumentException(
                sprintf('Field [%s] is not inheritable on %s.', $field, static::class)
            );
        }

        $list = $this->overridden_fields ?? [];
        $filtered = array_values(array_filter(
            $list,
            fn (string $f): bool => $f !== $field
        ));

        parent::setAttribute('overridden_fields', $filtered ?: null);

        return $this;
    }

    public function resetFieldOnAllVariants(string $field): int
    {
        if (! $this->isInheritableField($field)) {
            throw new InvalidArgumentException(
                sprintf('Field [%s] is not inheritable on %s.', $field, static::class)
            );
        }

        $variants = $this->children()
            ->whereJsonContains('overridden_fields', $field)
            ->select(['id', 'overridden_fields'])
            ->get();

        if ($variants->isEmpty()) {
            return 0;
        }

        // Group by the new shape so we issue one UPDATE per distinct resulting JSON.
        $groups = $variants->groupBy(function ($variant) use ($field) {
            $remaining = array_values(array_filter(
                $variant->overridden_fields ?? [],
                fn (string $f): bool => $f !== $field
            ));

            return $remaining === [] ? '__null__' : json_encode($remaining);
        });

        DB::transaction(function () use ($groups): void {
            foreach ($groups as $newShapeKey => $rows) {
                $newValue = $newShapeKey === '__null__' ? null : json_decode($newShapeKey, true);
                static::query()
                    ->whereKey($rows->pluck('id')->all())
                    ->update(['overridden_fields' => $newValue]);
            }
        });

        return $variants->count();
    }

    public function resetRelation(string $relation, mixed $key = null): static
    {
        if (! $this->isInheritableRelation($relation)) {
            throw new InvalidArgumentException(
                sprintf('Relation [%s] is not inheritable on %s.', $relation, static::class)
            );
        }

        $ownMethod = 'own' . ucfirst($relation);
        $rel = $this->{$ownMethod}();

        if (method_exists($rel, 'detach')) {
            // BelongsToMany / MorphToMany
            if ($key !== null) {
                $rel->detach($key);
            } else {
                $rel->detach();
            }
        } else {
            // HasMany — needs a foreign key for per-row deletion
            if ($key !== null) {
                $foreignKey = $this->resolveForeignKeyForInheritableRelation($relation);
                $rel->where($foreignKey, $key)->delete();
            } else {
                $rel->delete();
            }
        }

        return $this;
    }

    public function resetRelationOnAllVariants(string $relation, mixed $key = null): int
    {
        if (! $this->isInheritableRelation($relation)) {
            throw new InvalidArgumentException(
                sprintf('Relation [%s] is not inheritable on %s.', $relation, static::class)
            );
        }

        $ownMethod = 'own' . ucfirst($relation);
        $variantIds = $this->children()->pluck('id');

        if ($variantIds->isEmpty()) {
            return 0;
        }

        // Build a fresh "own" relation on a representative variant so we can read its
        // SQL shape without invoking it on each child individually.
        $sampleVariant = static::query()->whereKey($variantIds->first())->first();
        if (! $sampleVariant) {
            return 0;
        }

        $rel = $sampleVariant->{$ownMethod}();

        if ($rel instanceof BelongsToMany) {
            $pivotTable = $rel->getTable();
            $foreignPivotKey = $rel->getForeignPivotKeyName();
            $relatedPivotKey = $rel->getRelatedPivotKeyName();

            $query = DB::table($pivotTable)
                ->whereIn($foreignPivotKey, $variantIds);

            if ($key !== null) {
                $query->where($relatedPivotKey, $key);
            }

            if ($rel instanceof MorphToMany) {
                $query->where($rel->getMorphType(), $rel->getMorphClass());
            }

            $touched = $query->clone()->distinct()->pluck($foreignPivotKey)->count();

            $query->delete();

            return $touched;
        }

        if ($rel instanceof HasMany) {
            $relatedTable = $rel->getRelated()->getTable();
            $relatedForeignKey = $rel->getForeignKeyName();

            $query = DB::table($relatedTable)
                ->whereIn($relatedForeignKey, $variantIds);

            if ($key !== null) {
                $foreignKey = $this->resolveForeignKeyForInheritableRelation($relation);
                $query->where($foreignKey, $key);
            }

            $touched = $query->clone()->distinct()->pluck($relatedForeignKey)->count();

            $query->delete();

            return $touched;
        }

        throw new LogicException("Unsupported relation type for bulk reset: [$relation].");
    }

    /**
     * Map of inheritable HasMany relation name → foreign-key column on the related table.
     * BelongsToMany relations don't need this because they use detach().
     */
    protected function resolveForeignKeyForInheritableRelation(string $relation): string
    {
        return match ($relation) {
            'prices' => 'price_list_id',
            default => throw new LogicException("No HasMany foreign-key mapping for [$relation]."),
        };
    }

    /**
     * Resolve an inheritable HasMany/BelongsToMany relation, merging the variant's
     * own pivot/related rows with the parent's effective collection on the given key column.
     *
     * Sharp edge: a paired `getXAttribute` accessor without a matching `setXAttribute`
     * mutator means writes like `$model->x = [array]` fall through to setAttribute and
     * land in the raw attributes bag — subsequent reads of `$model->x` then return the
     * stored array instead of the resolved Collection. Today's Livewire write paths
     * Arr::pull these values before save, so the bug isn't observable; if a future
     * caller relies on round-trip read after assignment, add a setter.
     *
     * @param  string  $ownRelationMethod  Name of the "own" relation method (e.g. ownPrices)
     * @param  string  $resolvedRelation  Name as it appears in $inheritableRelations (e.g. prices)
     * @param  string  $foreignKeyOnRelated  Column that uniquely identifies the row within the
     *                                       relation set (e.g. price_list_id, category_id)
     */
    protected function resolveInheritedCollection(
        string $ownRelationMethod,
        string $resolvedRelation,
        string $foreignKeyOnRelated
    ): EloquentCollection {
        /** @var EloquentCollection $own */
        $own = $this->{$ownRelationMethod};

        if (! $this->isVariant() || ! $this->inheritanceEnabled()) {
            return $own;
        }

        $parent = $this->relationLoaded('parent') ? $this->parent : $this->parent()->first();
        if (! $parent) {
            return $own;
        }

        /** @var EloquentCollection $parentEffective */
        $parentEffective = $parent->{$resolvedRelation};

        $ownKeys = $own->pluck($foreignKeyOnRelated);
        $inherited = $parentEffective->whereNotIn($foreignKeyOnRelated, $ownKeys);

        return $own->concat($inherited)->values();
    }
}
