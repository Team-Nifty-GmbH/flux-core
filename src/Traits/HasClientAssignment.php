<?php

namespace FluxErp\Traits;

use FluxErp\Exceptions\SearchableException;
use FluxErp\Models\Client;
use FluxErp\Models\Scopes\UserClientScope;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Searchable;

trait HasClientAssignment
{
    public static function bootHasClientAssignment(): void
    {
        static::addGlobalScope(new UserClientScope());
    }

    /**
     * @throws SearchableException
     */
    public static function search($query = '', $callback = null)
    {
        $model = new static;
        if (! in_array(Searchable::class, class_uses_recursive($model))) {
            throw SearchableException::modelNotSearchable(static::class);
        }

        if (($user = Auth::user()) instanceof User
            && $model->isRelation('client')
            && ($relation = $model->client()) instanceof BelongsTo
        ) {
            $clients = $user->clients()->pluck('id')->toArray() ?: Client::query()->pluck('id')->toArray();

            return static::searchableSearch($query, $callback)->whereIn($relation->getForeignKeyName(), $clients);
        }

        return static::searchableSearch($query, $callback);
    }
}
