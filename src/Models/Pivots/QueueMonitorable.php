<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\QueueMonitor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class QueueMonitorable extends FluxPivot
{
    protected $table = 'queue_monitorable';

    protected function casts(): array
    {
        return [
            'notify_on_finish' => 'boolean',
        ];
    }

    // Relations
    /**
     * @return BelongsTo<QueueMonitor, $this>
     */
    public function queueMonitor(): BelongsTo
    {
        return $this->belongsTo(QueueMonitor::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function queueMonitorable(): MorphTo
    {
        return $this->morphTo();
    }
}
