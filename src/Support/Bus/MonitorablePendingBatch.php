<?php

namespace FluxErp\Support\Bus;

use FluxErp\Models\JobBatch;
use FluxErp\Notifications\QueueMonitor\Batch\BatchFinishedNotification;
use FluxErp\Notifications\QueueMonitor\Batch\BatchProcessingNotification;
use FluxErp\Notifications\QueueMonitor\Batch\BatchStartedNotification;
use FluxErp\Traits\MonitorsQueue;
use Illuminate\Bus\Batch;
use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Context;

class MonitorablePendingBatch extends PendingBatch
{
    public function __construct(Container $container, Collection $jobs)
    {
        parent::__construct($container, $jobs);

        $this->before(function (Batch $batch) {
            $jobBatch = app(JobBatch::class)->whereKey($batch->id)->first();

            $user = auth()->user();
            if (! $user && $context = Context::get('user')) {
                $context = explode(':', $context);
                $user = app($context[0])->find($context[1]);
            }

            if ($user && array_key_exists(MonitorsQueue::class, class_uses_recursive($user))) {
                $user->jobBatches()->attach($jobBatch);
                $user->notify(new BatchStartedNotification($jobBatch));
            }
        });

        $this->progress(function (Batch $batch) {
            $jobBatch = app(JobBatch::class)->whereKey($batch->id)->first();

            $jobBatch->users->each->notify(
                $jobBatch->isFinished()
                    ? new BatchFinishedNotification($jobBatch)
                    : new BatchProcessingNotification($jobBatch)
            );
        });

        $this->finally(function (Batch $batch) {
            $jobBatch = app(JobBatch::class)->whereKey($batch->id)->first();

            $jobBatch->users->each->notify(new BatchFinishedNotification($jobBatch));
        });
    }
}
