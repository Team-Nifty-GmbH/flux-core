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
use Illuminate\Support\Facades\Context;

class UserTenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Don't apply scope if no user is authenticated
        if (! Auth::hasUser()) {
            return;
        }

        // Cache tenant IDs in request-scoped Context to avoid multiple queries
        if (! Context::has('user_tenant_ids')) {
            Context::add('user_tenant_ids', ($user = Auth::user()) instanceof User
                ? $user->tenants()
                    ->withoutGlobalScope(UserTenantScope::class)
                    ->pluck('id')
                    ->toArray()
                : []);
        }

        $tenants = Context::get('user_tenant_ids');

        if (! $tenants) {
            return;
        }

        if ($model instanceof Tenant) {
            $builder->whereIntegerInRaw($model->getQualifiedKeyName(), $tenants);

            return;
        }

        if ($model->isRelation('tenant')
            && ($relation = $model->tenant()) instanceof BelongsTo
        ) {
            $builder->where(fn (Builder $query) => $query
                ->whereIntegerInRaw(
                    $relation->getQualifiedForeignKeyName(),
                    $tenants
                )
                ->orWhereNull($relation->getQualifiedForeignKeyName())
            );
        }

        if ($model->isRelation('tenants')
            && ($relation = $model->tenants()) instanceof BelongsToMany
        ) {
            if (is_null($builder->getQuery()->columns)) {
                $builder->select($builder->getQuery()->from . '.*');
            }

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
                        $tenants
                    )
                    ->orWhereNull('uts.' . $relation->getForeignPivotKeyName())
                );
        }
    }
}
