<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\Notifiable;

class NotificationSetting extends FluxModel
{
    use HasPackageFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'channel_value' => 'array',
        ];
    }
}
