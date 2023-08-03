<?php

namespace FluxErp\Traits;

use Illuminate\Database\Eloquent\SoftDeletes as BaseSoftDeletes;

trait SoftDeletes
{
    use BaseSoftDeletes {
        BaseSoftDeletes::initializeSoftDeletes as initializeSoftDeletesBase;
    }

    public function initializeSoftDeletes(): void
    {
        $this->initializeSoftDeletesBase();
        $this->hidden = array_merge($this->hidden, ['deleted_at']);
    }
}
