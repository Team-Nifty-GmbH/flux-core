<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\JobBatch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JobBatchable extends FluxPivot
{
    public $timestamps = false;

    protected $table = 'job_batchables';

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
