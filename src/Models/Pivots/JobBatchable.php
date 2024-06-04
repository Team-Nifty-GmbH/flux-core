<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\JobBatch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class JobBatchable extends Pivot
{
    protected $table = 'job_batchables';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'notify_on_finish' => 'boolean',
        ];
    }

    public function jobBatch(): BelongsTo
    {
        return $this->belongsTo(JobBatch::class);
    }

    public function jobBatchable(): MorphTo
    {
        return $this->morphTo();
    }
}
