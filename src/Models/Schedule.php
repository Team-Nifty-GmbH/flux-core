<?php

namespace FluxErp\Models;

use FluxErp\Enums\RepeatableTypeEnum;
use FluxErp\Models\Pivots\OrderSchedule;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Schedule extends FluxModel
{
    use HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => RepeatableTypeEnum::class,
            'cron' => 'array',
            'parameters' => 'array',
            'due_at' => 'datetime',
            'ends_at' => 'datetime',
            'last_success' => 'datetime',
            'last_run' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Relations
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_schedule')
            ->using(OrderSchedule::class);
    }
}
