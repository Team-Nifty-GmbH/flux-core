<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use FluxErp\Models\Pivots\QueueMonitorable;
use FluxErp\Notifications\QueueMonitor\Job\JobFinishedNotification;
use FluxErp\Notifications\QueueMonitor\Job\JobProcessingNotification;
use FluxErp\Notifications\QueueMonitor\Job\JobStartedNotification;
use FluxErp\States\QueueMonitor\Failed;
use FluxErp\States\QueueMonitor\QueueMonitorState;
use FluxErp\States\QueueMonitor\Succeeded;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\MonitorsQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Context;
use Spatie\ModelStates\HasStates;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class QueueMonitor extends Model
{
    use BroadcastsEvents, HasFrontendAttributes, HasStates, MassPrunable;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'state' => QueueMonitorState::class,
            'failed' => 'bool',
            'retried' => 'bool',
            'queued_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'data' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public static function booted(): void
    {
        static::created(function (QueueMonitor $monitor) {
            $user = auth()->user();
            if (! $user && $context = Context::get('user')) {
                $context = explode(':', $context);
                $user = Relation::getMorphedModel($context[0])->whereKey($context[1])->first();
            }

            if ($user && array_key_exists(MonitorsQueue::class, class_uses_recursive($user))) {
                $user->queueMonitors()->attach($monitor);
                // ensure that the started notification is only sent once
                if (! $monitor->job_batch_id) {
                    $user->notify(new JobStartedNotification($monitor));
                }
            }
        });

        static::updated(function (QueueMonitor $monitor) {
            if (! $monitor->job_batch_id) {
                $monitor->users->each->notify(
                    $monitor->isFinished()
                        ? new JobFinishedNotification($monitor)
                        : new JobProcessingNotification($monitor)
                );
            }
        });
    }

    public function addresses(): MorphToMany
    {
        return $this->morphedByMany(Address::class, 'queue_monitorable', 'queue_monitorables');
    }

    public function jobBatch(): BelongsTo
    {
        return $this->belongsTo(JobBatch::class);
    }

    public function queueMonitorables(): HasMany
    {
        return $this->hasMany(QueueMonitorable::class);
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'queue_monitorable', 'queue_monitorables');
    }

    public function prunable(): Builder
    {
        return $this->where('finished_at', '<', now()->subDays(30));
    }

    public function broadcastWith(): array
    {
        // This ensures the payload doesnt get too large
        return ['model' => Arr::except($this->withoutRelations()->toArray(), ['exception'])];
    }

    public function getJobName(): string
    {
        return class_exists($this->name)
            ? __(str(class_basename($this->name))->headline()->toString())
            : $this->name;
    }

    public function getStartedAtExact(): ?Carbon
    {
        if (is_null($this->started_at_exact)) {
            return null;
        }

        return Carbon::parse($this->started_at_exact);
    }

    public function getFinishedAtExact(): ?Carbon
    {
        if (is_null($this->finished_at_exact)) {
            return null;
        }

        return Carbon::parse($this->finished_at_exact);
    }

    public function getRemainingSeconds(?Carbon $now = null): float
    {
        return $this->getRemainingInterval($now)->totalSeconds;
    }

    public function getRemainingInterval(?Carbon $now = null): CarbonInterval
    {
        $now ??= Carbon::now();

        if (! $this->progress
            || is_null($this->started_at)
            || $this->isFinished()
            || $this->progress === 0.0
        ) {
            return CarbonInterval::seconds(0);
        }

        $timeDiff = $now->getTimestamp() - $this->started_at->getTimestamp();
        if ($timeDiff === 0) {
            return CarbonInterval::seconds(0);
        }

        try {
            return CarbonInterval::seconds(
                (1 - $this->progress) / ($this->progress / $timeDiff)
            )->cascade();
        } catch (\Throwable) {
            return CarbonInterval::seconds(0);
        }
    }

    public function getElapsedSeconds(?Carbon $end = null): int
    {
        return $this->getElapsedInterval($end)->seconds;
    }

    public function getElapsedInterval(?Carbon $end = null): CarbonInterval
    {
        if (is_null($end)) {
            $end = $this->getFinishedAtExact() ?? $this->finished_at ?? now();
        }

        $startedAt = $this->getStartedAtExact() ?? $this->started_at;

        if (is_null($startedAt)) {
            return CarbonInterval::seconds(0);
        }

        return $startedAt->diffAsCarbonInterval($end);
    }

    public function getException(bool $rescue = true): ?\Throwable
    {
        if (is_null($this->exception_class)) {
            return null;
        }

        if (! $rescue) {
            return new $this->exception_class($this->exception_message);
        }

        try {
            return new $this->exception_class($this->exception_message);
        } catch (\Exception) {
            return null;
        }
    }

    public function isFinished(): bool
    {
        if ($this->hasFailed()) {
            return true;
        }

        return ! is_null($this->finished_at);
    }

    public function hasFailed(): bool
    {
        return is_a($this->status, Failed::class, true);
    }

    public function hasSucceeded(): bool
    {
        if (! $this->isFinished()) {
            return false;
        }

        return ! $this->hasFailed();
    }

    public function retry(): void
    {
        $this->retried = true;
        $this->save();

        $response = Artisan::call('queue:retry', ['id' => $this->job_uuid]);

        if ($response !== 0) {
            throw new \Exception(Artisan::output());
        }
    }

    public function canBeRetried(): bool
    {
        return ! $this->retried
            && ! is_a($this->status, Failed::class, true)
            && ! is_null($this->job_uuid);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query
            ->orderBy('started_at', 'desc')
            ->orderBy('started_at_exact', 'desc');
    }

    public function scopeLastHour(Builder $query): void
    {
        $query->where('started_at', '>', now()->subHours(1));
    }

    public function scopeToday(Builder $query): void
    {
        $query->whereRaw('DATE(started_at) = ?', [now()->subHours(1)->format('Y-m-d')]);
    }

    public function scopeFailed(Builder $query): void
    {
        $query->whereState('state', Failed::class);
    }

    public function scopeSucceeded(Builder $query): void
    {
        $query->whereState('state', Succeeded::class);
    }
}
