<?php

namespace FluxErp\Traits;

use FluxErp\Models\Scopes\FamilyTreeScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasParentChildRelations
{
    protected function getParentKeyAttribute(): string
    {
        return 'parent_id';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, $this->getParentKeyAttribute());
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, $this->getParentKeyAttribute());
    }

    public function scopeFamilyTree(): void
    {
        static::addGlobalScope(resolve_static(FamilyTreeScope::class, 'class'));
    }

    public static function familyTree(): Builder
    {
        static::addGlobalScope(resolve_static(FamilyTreeScope::class, 'class'));

        return static::query();
    }
}
