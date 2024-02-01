<?php

namespace FluxErp\Traits;

use FluxErp\Models\Scopes\UserClientScope;

trait HasClientAssignment
{
    public static function bootHasClientAssignment(): void
    {
        static::addGlobalScope(new UserClientScope());
    }
}
