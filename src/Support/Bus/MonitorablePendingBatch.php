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
use Illuminate\Support\Facades\Log;
use Throwable;

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
                $user = morphed_model($context[0])::query()->whereKey($context[1])->first();
            }

            if ($user && array_key_exists(MonitorsQueue::class, class_uses_recursive($user))) {
                $user->jobBatches()->attach($jobBatch);
                try {
                    $user->notify(new BatchStartedNotification($jobBatch));
                } catch (Throwable $e) {
                    Log::error($e);
                }
            }
        });

        $this->progress(function (Batch $batch) {
            $jobBatch = app(JobBatch::class)->whereKey($batch->id)->first();

            $jobBatch->users->each(function ($user) use ($jobBatch) {
                try {
                    $user->notify(
                        $jobBatch->isFinished()
                            ? new BatchFinishedNotification($jobBatch)
                            : new BatchProcessingNotification($jobBatch)
                    );
                } catch (Throwable $e) {
                    Log::error($e);
                }
            });
        });

        $this->finally(function (Batch $batch) {
            $jobBatch = app(JobBatch::class)->whereKey($batch->id)->first();

            $jobBatch->users->each(function ($user) use ($jobBatch) {
                try {
                    $user->notify(new BatchFinishedNotification($jobBatch));
                } catch (Throwable $e) {
                    Log::error($e);
                }
            });
        });
    }
}
