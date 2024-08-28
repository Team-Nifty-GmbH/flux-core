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
    protected static array $clients = [];

    public function apply(Builder $builder, Model $model): void
    {
        // Don't apply scope if no user is authenticated
        if (! Auth::hasUser()) {
            return;
        }

        // write the clients to a static property to avoid multiple queries if the scope is used multiple times
        static::$clients ??= ($user = Auth::user()) instanceof User ?
            $user->clients()
                ->withoutGlobalScope(UserClientScope::class)
                ->pluck('id')
                ->toArray() : [];

        if (! static::$clients) {
            return;
        }

        if ($model instanceof Client) {
            $builder->whereIntegerInRaw($model->getQualifiedKeyName(), static::$clients);

            return;
        }

        if ($model->isRelation('client')
            && ($relation = $model->client()) instanceof BelongsTo
        ) {
            $builder->where(fn (Builder $query) => $query
                ->whereIntegerInRaw(
                    $relation->getQualifiedForeignKeyName(),
                    static::$clients
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
                        static::$clients
                    )
                    ->orWhereNull('ucs.' . $relation->getForeignPivotKeyName())
                );
        }
    }
}
