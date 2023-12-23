<?php

namespace FluxErp\Models;

use FluxErp\Enums\RepeatableTypeEnum;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class Schedule extends Model
{
    use BroadcastsEvents, HasUuid, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'type' => RepeatableTypeEnum::class,
        'cron' => 'array',
        'parameters' => 'array',
        'due_at' => 'datetime',
        'last_success' => 'datetime',
        'last_run' => 'datetime',
        'is_active' => 'boolean',
    ];
}
