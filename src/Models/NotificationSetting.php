<?php

namespace FluxErp\Models;

use FluxErp\Traits\Notifiable;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasPackageFactory, Notifiable;

    protected $casts = [
        'is_active' => 'boolean',
        'channel_value' => 'array',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
