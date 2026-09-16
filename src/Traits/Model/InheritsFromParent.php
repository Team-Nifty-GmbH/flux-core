<?php

namespace FluxErp\Traits\Model;

use FluxErp\Settings\ProductSettings;

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
        return app(ProductSettings::class)->variant_inheritance_enabled;
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

    public function markOverridesForDirtyFields(): void
    {
        if (! $this->isVariant() || ! $this->inheritanceEnabled()) {
            return;
        }

        $parent = $this->relationLoaded('parent') ? $this->parent : $this->parent()->first();
        if (! $parent) {
            return;
        }

        $list = $this->overridden_fields ?? [];

        foreach ($this->getInheritableFields() as $field) {
            if (! $this->isDirty($field)) {
                continue;
            }

            // Loose comparison: uncast decimal columns (e.g. weight_gram) come back from a
            // fresh DB load as numeric strings ("100.0000000000") while the in-memory,
            // just-assigned value is a plain int/float — strict !== would false-positive
            // on every write to those fields even when the value is unchanged.
            // The explicit null check guards against PHP's loose equality treating
            // null as equal to '' or 0 — without it, clearing a field to '' while the
            // parent is null would collapse to "not different" and silently drop the override.
            $new = $this->getAttribute($field);
            $old = $parent->getAttribute($field);
            $differs = (is_null($new) !== is_null($old)) || $new != $old;
            $isMarked = in_array($field, $list, strict: true);

            if ($differs && ! $isMarked) {
                $list[] = $field;
            } elseif (! $differs && $isMarked) {
                $list = array_values(array_filter($list, fn (string $overriddenField): bool => $overriddenField !== $field));
            }
        }

        $this->overridden_fields = $list ?: null;
    }
}
