<?php

namespace FluxErp\Traits;

use FluxErp\Models\JobBatch;
use FluxErp\Models\QueueMonitor;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait MonitorsQueue
{
    public function jobBatches()
    {
        return $this->morphToMany(JobBatch::class, 'job_batchable', 'job_batchables');
    }

    public function queueMonitors(): MorphToMany
    {
        return $this->morphToMany(QueueMonitor::class, 'queue_monitorable', 'queue_monitorables');
    }
}
