<?php

namespace FluxErp\Traits\Model;

use FluxErp\Models\Scopes\UserClientScope;

trait HasClientAssignment
{
    public static function bootHasClientAssignment(): void
    {
        static::addGlobalScope(app(UserClientScope::class));
    }
}
