<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\BroadcastsEvents;
use FluxErp\Traits\Model\HasModelPermission;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class FluxModel extends Model
{
    use BroadcastsEvents, HasModelPermission, ResolvesRelationsThroughContainer;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public static function removeGlobalScopes(array $scopes): void
    {
        foreach ($scopes as $scope) {
            unset(static::$globalScopes[static::class][$scope]);
        }
    }

    public static function withTemporaryGlobalScopes(array $scopes): Builder
    {
        static::addGlobalScopes($scopes);

        $scopeKeys = array_keys($scopes);

        return static::query()->afterQuery(function () use ($scopeKeys): void {
            static::removeGlobalScopes($scopeKeys);
        });
    }
}
