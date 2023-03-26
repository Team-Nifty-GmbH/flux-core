<?php

namespace FluxErp\Traits;

use Illuminate\Database\Eloquent\SoftDeletes as BaseSoftDeletes;

trait SoftDeletes
{
    use BaseSoftDeletes;

    public function initializeSoftDeletes(): void
    {
        $this->hidden = array_merge($this->hidden, ['deleted_at']);
    }
}
