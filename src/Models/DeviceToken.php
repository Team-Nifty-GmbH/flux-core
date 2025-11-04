<?php

namespace FluxErp\Models;

use FluxErp\Enums\DevicePlatformEnum;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DeviceToken extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'platform' => DevicePlatformEnum::class,
        ];
    }

    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }
}
