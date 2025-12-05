<?php

namespace FluxErp\Models\Scopes;

use FluxErp\Models\Tenant;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class UserTenantScope implements Scope
{
    protected static array $tenants = [];

    public function apply(Builder $builder, Model $model): void
    {
        // Don't apply scope if no user is authenticated
        if (! Auth::hasUser()) {
            return;
        }

        // write the tenants to a static property to avoid multiple queries if the scope is used multiple times
        static::$tenants ??= ($user = Auth::user()) instanceof User ?
            $user->tenants()
                ->withoutGlobalScope(UserTenantScope::class)
                ->pluck('id')
                ->toArray() : [];

        if (! static::$tenants) {
            return;
        }

        if ($model instanceof Tenant) {
            $builder->whereIntegerInRaw($model->getQualifiedKeyName(), static::$tenants);

            return;
        }

        if ($model->isRelation('tenant')
            && ($relation = $model->tenant()) instanceof BelongsTo
        ) {
            $builder->where(fn (Builder $query) => $query
                ->whereIntegerInRaw(
                    $relation->getQualifiedForeignKeyName(),
                    static::$tenants
                )
                ->orWhereNull($relation->getQualifiedForeignKeyName())
            );
        }

        if ($model->isRelation('tenants')
            && ($relation = $model->tenants()) instanceof BelongsToMany
        ) {
            // uts = UserTenantScope
            $builder->leftJoin(
                $relation->getTable() . ' AS uts',
                'uts.' . $relation->getForeignPivotKeyName(),
                '=',
                $relation->getQualifiedParentKeyName()
            )
                ->where(fn (Builder $query) => $query
                    ->whereIntegerInRaw(
                        'uts.' . $relation->getRelatedPivotKeyName(),
                        static::$tenants
                    )
                    ->orWhereNull('uts.' . $relation->getForeignPivotKeyName())
                );
        }
    }
}
