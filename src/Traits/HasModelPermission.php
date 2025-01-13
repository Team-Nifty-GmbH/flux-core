<?php

namespace FluxErp\Traits;

use FluxErp\Models\Permission;
use FluxErp\Models\Scopes\ModelPermissionCheckScope;
use Illuminate\Support\Collection;
use Spatie\Permission\Guard;

trait HasModelPermission
{
    protected static function bootHasModelPermission(): void
    {
        if (auth()->user() && ! auth()->user()->hasRole('Super Admin')) {
            static::addGlobalScope(app(ModelPermissionCheckScope::class));
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
