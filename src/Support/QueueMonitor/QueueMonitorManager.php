<?php

namespace FluxErp\Support\QueueMonitor;

use FluxErp\Contracts\ShouldBeMonitored;
use FluxErp\Models\QueueMonitor;
use FluxErp\States\QueueMonitor\Failed;
use FluxErp\States\QueueMonitor\Queued;
use FluxErp\States\QueueMonitor\Running;
use FluxErp\States\QueueMonitor\Stale;
use FluxErp\States\QueueMonitor\Succeeded;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Carbon;

class QueueMonitorManager
{
    public static function handle(JobQueued|JobProcessing|JobProcessed|JobFailed|JobExceptionOccurred $event): void
    {
        $method = lcfirst(class_basename($event));

        if (method_exists(static::class, $method) && static::shouldBeMonitored($event->job)) {
            static::$method($event);
        }
    }

    protected static function jobQueued(JobQueued $event): void
    {
        app(QueueMonitor::class)->query()->create([
            'job_batch_id' => $event->job->batchId ?? null,
            'job_id' => $event->id,
            'name' => get_class(static::getJobClass($event->job)),
            'queue' => $event->job->queue ?: 'default',
            'state' => Queued::class,
            'queued_at' => now(),
            'data' => $data ?? null,
        ]);
    }

    protected static function jobProcessing(JobProcessing $event): void
    {
        $now = Carbon::now();

        $monitor = app(QueueMonitor::class)
            ->query()
            ->where('job_id', $jobId = static::getJobId($event->job))
            ->where('queue', $event->job->getQueue() ?? config('queue.default'))
            ->whereState('state', Queued::class)
            ->firstOrNew();

        $monitor->fill([
            'job_uuid' => $event->job->uuid(),
            'job_id' => static::getJobId($event->job),
            'queue' => $event->job->getQueue() ?: config('queue.default'),
            'name' => static::getJobClass($event->job),
            'started_at' => $now,
            'started_at_exact' => $now->format('Y-m-d H:i:s.u'),
            'attempt' => $event->job->attempts(),
            'state' => Running::class,
        ]);
        $monitor->save();

        // Mark jobs with same job id (different execution) as stale
        app(QueueMonitor::class)->query()
            ->whereNot('id', $monitor->id)
            ->where('job_id', $jobId)
            ->whereNotState('state', Failed::class)
            ->whereNull('finished_at')
            ->each(function (QueueMonitor $monitor) {
                $monitor->update([
                    'finished_at' => $now = Carbon::now(),
                    'finished_at_exact' => $now->format('Y-m-d H:i:s.u'),
                    'state' => Stale::class,
                ]);
            });
    }

    protected static function jobProcessed(JobProcessed $event): void
    {
        static::jobFinished($event->job, Succeeded::class);
    }

    protected static function jobExceptionOccurred(JobExceptionOccurred $event): void
    {
        static::jobFinished($event->job, Failed::class, $event->exception);
    }

    protected static function jobFailed(JobFailed $event): void
    {
        static::jobFinished($event->job, Failed::class);
    }

    protected static function jobFinished(Job $job, string $state, ?\Throwable $exception = null): void
    {
        $monitor = app(QueueMonitor::class)
            ->query()
            ->where('job_id', static::getJobId($job))
            ->where('attempt', $job->attempts())
            ->orderByDesc('started_at')
            ->first();

        if (! $monitor) {
            return;
        }

        /** @var ShouldBeMonitored $resolvedJob */
        $resolvedJob = static::getJobClass($job);
        $keepMonitorOnSuccess = method_exists($resolvedJob, 'keepMonitorOnSuccess')
            ? $resolvedJob::keepMonitorOnSuccess()
            : true;

        if (is_null($exception) && ! $keepMonitorOnSuccess) {
            $monitor->delete();

            return;
        }

        $now = Carbon::now();

        // If the job encounters an exception but hasn't failed (i.e., it hasn't reached the maximum
        // number of tries and exceptions), it will be pushed back into the queue.
        // Additionally, if the job is processed but then released, it will also be returned to the queue.
        if (
            (is_a($state, Failed::class, true) && ! $job->hasFailed())
            || (is_a($state, Succeeded::class, true) && $job->isReleased())
        ) {
            $state = Queued::class;
        }

        $attributes = [
            'finished_at' => $now,
            'finished_at_exact' => $now->format('Y-m-d H:i:s.u'),
            'state' => $state,
        ];

        if (is_a($state, Succeeded::class, true)) {
            $attributes['progress'] = 1;
        }

        if (! is_null($exception)) {
            $attributes['exception'] = serialize($exception);
            $attributes['exception_message'] = $exception->getMessage();
            $attributes['exception_class'] = get_class($exception);
        }

        $monitor->update($attributes);
    }

    public static function shouldBeMonitored(object $job): bool
    {
        return is_a(
            static::getJobClass($job),
            ShouldBeMonitored::class,
            true
        );
    }

    public static function getJobClass(object $job): object|string
    {
        return method_exists($job, 'resolveName') ? $job->resolveName() : $job;
    }

    public static function getJobId(Job $job): string
    {
        return $job->getJobId() ?? md5($job->getRawBody());
    }
}
