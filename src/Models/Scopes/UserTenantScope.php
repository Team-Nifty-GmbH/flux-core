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
            $pivotTable = $relation->getTable();
            $foreignPivotKey = $relation->getForeignPivotKeyName();
            $relatedPivotKey = $relation->getRelatedPivotKeyName();
            $parentKey = $relation->getQualifiedParentKeyName();

            $builder->where(fn (Builder $query) => $query
                ->whereExists(function ($sub) use ($pivotTable, $foreignPivotKey, $relatedPivotKey, $parentKey, $tenants): void {
                    $sub->from($pivotTable)
                        ->whereColumn($pivotTable . '.' . $foreignPivotKey, $parentKey)
                        ->whereIntegerInRaw($pivotTable . '.' . $relatedPivotKey, $tenants);
                })
                ->orWhereDoesntExist(function ($sub) use ($pivotTable, $foreignPivotKey, $parentKey): void {
                    $sub->from($pivotTable)
                        ->whereColumn($pivotTable . '.' . $foreignPivotKey, $parentKey);
                })
            );
        }
    }
}
