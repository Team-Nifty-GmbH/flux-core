<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\Notifiable;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasPackageFactory, Notifiable;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'channel_value' => 'array',
        ];
    }
}
