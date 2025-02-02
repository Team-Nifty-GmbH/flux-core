<?php

namespace FluxErp\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class FamilyTreeScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->with('children');
    }
}
