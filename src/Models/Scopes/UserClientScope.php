<?php

namespace FluxErp\Models\Scopes;

use FluxErp\Models\Client;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class UserClientScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Don't apply scope if no user is authenticated
        if (! Auth::hasUser()) {
            return;
        }

        $clients = ($user = Auth::user()) instanceof User ?
            $user->clients()
                ->withoutGlobalScope(UserClientScope::class)
                ->pluck('id')
                ->toArray() : [];

        if (! $clients) {
            return;
        }

        if ($model instanceof Client) {
            $builder->whereIntegerInRaw($model->getQualifiedKeyName(), $clients);

            return;
        }

        if ($model->isRelation('client')
            && ($relation = $model->client()) instanceof BelongsTo
        ) {
            $builder->where(fn (Builder $query) => $query
                ->whereIntegerInRaw(
                    $relation->getQualifiedForeignKeyName(),
                    $clients
                )
                ->orWhereNull($relation->getQualifiedForeignKeyName())
            );
        }

        if ($model->isRelation('clients')
            && ($relation = $model->clients()) instanceof BelongsToMany
        ) {
            // ucs = UserClientScope
            $builder->leftJoin(
                $relation->getTable() . ' AS ucs',
                'ucs.' . $relation->getForeignPivotKeyName(),
                '=',
                $relation->getQualifiedParentKeyName()
            )
                ->where(fn (Builder $query) => $query
                    ->whereIntegerInRaw(
                        'ucs.' . $relation->getRelatedPivotKeyName(),
                        $clients
                    )
                    ->orWhereNull('ucs.' . $relation->getForeignPivotKeyName())
                );
        }
    }
}
