<?php

namespace FluxErp\Traits;

use FluxErp\Models\Scopes\FamilyTreeScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

trait HasParentChildRelations
{
    public static function familyTree(): Builder
    {
        static::addGlobalScope(resolve_static(FamilyTreeScope::class, 'class'));

        return static::query();
    }

    public function ancestorKeys(): array
    {
        $table = $this->getTable();
        $keyName = $this->getKeyName();
        $keyValue = $this->getKey();
        $parentKeyName = $this->getParentKeyAttribute();

        return DB::table(
            DB::raw("
                (WITH RECURSIVE parent_items AS (
                    SELECT {$keyName}, {$parentKeyName}
                    FROM {$table}
                    WHERE {$keyName} = {$keyValue}

                    UNION ALL

                    SELECT parent.{$keyName}, parent.{$parentKeyName}
                    FROM {$table} parent
                    INNER JOIN parent_items child ON parent.{$keyName} = child.{$parentKeyName}
                )
                SELECT {$keyName} as id
                FROM parent_items
                WHERE {$keyName} != {$keyValue}) as ancestors
            ")
        )
            ->pluck('id')
            ->toArray();
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, $this->getParentKeyAttribute());
    }

    public function descendantKeys(): array
    {
        $table = $this->getTable();
        $keyName = $this->getKeyName();
        $keyValue = $this->getKey();
        $parentKeyName = $this->getParentKeyAttribute();

        return DB::table(
            DB::raw("
                (WITH RECURSIVE child_items AS (
                    SELECT {$keyName}
                    FROM {$table}
                    WHERE {$keyName} = {$keyValue}

                    UNION ALL

                    SELECT child.{$keyName}
                    FROM {$table} child
                    INNER JOIN child_items parent ON child.{$parentKeyName} = parent.{$keyName}
                )
                SELECT {$keyName} as id
                FROM child_items
                WHERE {$keyName} != {$keyValue}) as descendants
            ")
        )
            ->pluck('id')
            ->toArray();
    }

    public function getAllAncestorsQuery(): Builder
    {
        return static::query()->whereKey($this->ancestorKeys());
    }

    public function getAllDescendantsQuery(): Builder
    {
        return static::query()->whereKey($this->descendantKeys());
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, $this->getParentKeyAttribute());
    }

    protected function getParentKeyAttribute(): string
    {
        return 'parent_id';
    }
}
