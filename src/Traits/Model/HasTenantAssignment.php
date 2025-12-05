<?php

namespace FluxErp\Traits\Model;

use FluxErp\Models\Scopes\UserTenantScope;

trait HasTenantAssignment
{
    public static function bootHasTenantAssignment(): void
    {
        static::addGlobalScope(app(UserTenantScope::class));
    }
}
