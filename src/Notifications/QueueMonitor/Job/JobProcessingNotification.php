<?php

namespace FluxErp\Notifications\QueueMonitor\Job;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Models\QueueMonitor;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\BroadcastNowChannel;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;

class JobProcessingNotification extends Notification implements HasToastNotification
{
    public function __construct(public QueueMonitor $model)
    {
        $this->id = $this->model->job_batch_id ?? $this->model->job_id;
    }

    public function via(object $notifiable): array
    {
        return [BroadcastNowChannel::class];
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->notifiable($notifiable)
            ->title(__(':job_name is processing', ['job_name' => __($this->model->getJobName())]))
            ->description(__(':time remaining', ['time' => $this->model->getRemainingInterval()]).
                ($this->model->message ? '<br>'.$this->model->message : '')
            )
            ->icon('info')
            ->timeout(0)
            ->attributes([
                'progress' => $this->model->jobBatch?->progress ?? $this->model->progress,
                'state' => $this->model->state,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }
}
