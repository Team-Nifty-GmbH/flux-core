<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;

class EventSubscription extends Model
{
    use Filterable, HasPackageFactory;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_broadcast' => 'boolean',
        'is_notifiable' => 'boolean',
    ];
}
