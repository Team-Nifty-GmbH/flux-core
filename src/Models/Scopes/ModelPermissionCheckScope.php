<?php

namespace FluxErp\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ModelPermissionCheckScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! $model::hasPermission()) {
            return;
        }

        $relevantPermissions = $model::getRelevantPermissions();

        if ($relevantPermissions->isNotEmpty() && ! auth()->user()->hasAnyPermission($relevantPermissions)) {
            $builder->whereRaw('1 = 0');
        }
    }
}
