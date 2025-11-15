<?php

namespace FluxErp\Traits;

use FluxErp\Models\Scopes\UserTenantScope;

trait HasTenantAssignment
{
    public static function bootHasTenantAssignment(): void
    {
        static::addGlobalScope(app(UserTenantScope::class));
    }
}
