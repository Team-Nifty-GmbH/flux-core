<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\JobBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JobBatchable extends FluxPivot
{
    protected $table = 'job_batchables';

    protected $primaryKey = null;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'notify_on_finish' => 'boolean',
        ];
    }

    // Relations
    /**
     * @return BelongsTo<JobBatch, $this>
     */
    public function jobBatch(): BelongsTo
    {
        return $this->belongsTo(JobBatch::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function jobBatchable(): MorphTo
    {
        return $this->morphTo();
    }
}
