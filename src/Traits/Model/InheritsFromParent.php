<?php

namespace FluxErp\Traits\Model;

use FluxErp\Models\Tenant;

/**
 * Provides whitelist-driven inheritance helpers for child models that resolve
 * fields and relations from a parent record.
 *
 * Consuming classes must declare:
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
}
