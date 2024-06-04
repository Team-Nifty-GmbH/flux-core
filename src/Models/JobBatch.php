<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use FluxErp\Models\Pivots\JobBatchable;
use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Bus;

class JobBatch extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [
        'id',
    ];

    public function casts(): array
    {
        return [
            'options' => 'array',
            'failed_job_ids' => 'array',
            'canceled_at' => 'datetime',
            'created_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function getBatch(): ?Batch
    {
        return Bus::findBatch($this->id);
    }

    public function jobBatchables(): HasMany
    {
        return $this->hasMany(JobBatchable::class);
    }

    public function queueMonitors(): HasMany
    {
        return $this->hasMany(QueueMonitor::class);
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'job_batchable', 'job_batchables');
    }

    public function getProgress(): float
    {
        if ($this->isFinished()) {
            return 1;
        }

        if ($this->total_jobs > 0) {
            return $this->getProcessedJobs() / $this->total_jobs;
        }

        return 0;
    }

    public function getProcessedJobs(): int
    {
        return $this->total_jobs - $this->pending_jobs - $this->failed_jobs;
    }

    public function getRemainingInterval(?Carbon $now = null): CarbonInterval
    {
        $now ??= Carbon::now();

        if (! in_array($this->getProgress(), [0.0, 1.0])
            || is_null($this->created_at)
            || $this->isFinished()
        ) {
            return CarbonInterval::seconds(0);
        }

        $timeDiff = $now->getTimestamp() - $this->created_at->getTimestamp();
        if ($timeDiff === 0) {
            return CarbonInterval::seconds(0);
        }

        try {
            return CarbonInterval::seconds(
                (1 - $this->getProgress()) / ($this->getProgress() / $timeDiff)
            )->cascade();
        } catch (\Throwable) {
            return CarbonInterval::seconds(0);
        }
    }

    public function isFinished(): bool
    {
        return ! is_null($this->finished_at);
    }
}
