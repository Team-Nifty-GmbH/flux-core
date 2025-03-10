<?php

namespace FluxErp\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ModelPermissionCheckScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        return;
        if (! auth()->check() || ! $model::hasPermission()) {
            return;
        }

        $relevantPermissions = $model::getRelevantPermissions();

        if (
            $relevantPermissions->isNotEmpty()
            && ! auth()->user()->hasAnyPermission($relevantPermissions)
            && ! auth()->user()->hasRole('Super Admin')
        ) {
            $builder->whereRaw('1 = 0');
        }
    }
}
