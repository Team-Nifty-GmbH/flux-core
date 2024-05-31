<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\QueueMonitor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class QueueMonitorable extends Pivot
{
    protected $table = 'queue_monitorables';

    public $timestamps = false;

    public function queueMonitor(): BelongsTo
    {
        return $this->belongsTo(QueueMonitor::class);
    }

    public function queueMonitorable(): MorphTo
    {
        return $this->morphTo();
    }
}
