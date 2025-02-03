<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Setting extends FluxModel
{
    use HasPackageFactory, HasUuid;

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
