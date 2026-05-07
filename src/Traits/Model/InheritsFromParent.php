<?php

namespace FluxErp\Traits\Model;

use FluxErp\Models\Tenant;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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
 * Inheritance is gated by the install's default tenant flag
 * `product_variant_inheritance_enabled`. The check uses Tenant::default(),
 * meaning the toggle applies install-wide rather than per-tenant-of-the-record.
 * This matches the spec contract.
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
        $tenant = resolve_static(Tenant::class, 'default');

        return (bool) ($tenant?->product_variant_inheritance_enabled);
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

        $touched = 0;

        $this->children()->each(function (self $variant) use ($field, &$touched): void {
            if ($variant->overrides($field)) {
                $variant->resetField($field);
                $variant->save();
                $touched++;
            }
        });

        return $touched;
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

        $touched = 0;
        $ownMethod = 'own' . ucfirst($relation);

        $this->children()->each(function (self $variant) use ($relation, $key, $ownMethod, &$touched): void {
            $countBefore = $variant->{$ownMethod}()->count();
            $variant->resetRelation($relation, $key);
            $countAfter = $variant->{$ownMethod}()->count();

            if ($countAfter < $countBefore) {
                $touched++;
            }
        });

        return $touched;
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
