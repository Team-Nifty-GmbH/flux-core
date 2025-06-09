<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\OrderPosition;
use FluxErp\Models\Task;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPositionTask extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $table = 'order_position_task';

    public function orderPosition(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class, 'order_position_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
