<?php

namespace FluxErp\Traits;

use FluxErp\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Permission\Guard;

trait HasModelPermission
{
    protected static function bootHasModelPermission(): void
    {
        if (auth()->user() && ! auth()->user()->hasRole('Super Admin')) {
            static::addGlobalScope('permissionCheck', function (Builder $builder) {
                if (! static::hasPermission()) {
                    return;
                }

                $relevantPermissions = static::getRelevantPermissions();

                if ($relevantPermissions->isNotEmpty() && ! auth()->user()->hasAnyPermission($relevantPermissions)) {
                    $builder->whereRaw('1 = 0');
                }
            });
        }
    }

    public static function hasPermission(): bool
    {
        return true;
    }

    public static function getRelevantPermissions(): Collection
    {
        return resolve_static(Permission::class, 'query')
            ->where(
                'name',
                'model.' . morph_alias(static::class) . '.get'
            )
            ->where('guard_name', Guard::getDefaultName(auth()->user()::class))
            ->get(['id', 'name']);
    }
}
