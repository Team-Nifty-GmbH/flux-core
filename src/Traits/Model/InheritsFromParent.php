<?php

namespace FluxErp\Traits\Model;

use FluxErp\Models\Tenant;
use InvalidArgumentException;

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
}
