<?php

namespace FluxErp\Models;

use FluxErp\Enums\DevicePlatformEnum;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
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
