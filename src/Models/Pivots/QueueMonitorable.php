<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\QueueMonitor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class QueueMonitorable extends FluxPivot
{
    protected $table = 'queue_monitorables';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'notify_on_finish' => 'boolean',
        ];
    }

    public function queueMonitor(): BelongsTo
    {
        return $this->belongsTo(QueueMonitor::class);
    }

    public function queueMonitorable(): MorphTo
    {
        return $this->morphTo();
    }
}
