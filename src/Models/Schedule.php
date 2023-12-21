<?php

namespace FluxErp\Models;

use FluxErp\Enums\RepeatableTypeEnum;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasUuid, SoftDeletes;

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
