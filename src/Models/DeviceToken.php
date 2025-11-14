<?php

namespace FluxErp\Models;

use FluxErp\Enums\DevicePlatformEnum;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DeviceToken extends FluxModel
{
    use HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'platform' => DevicePlatformEnum::class,
            'is_active' => 'boolean',
        ];
    }

    public function authenticatable(): MorphTo
    {
        return $this->morphTo('authenticatable');
    }
}
