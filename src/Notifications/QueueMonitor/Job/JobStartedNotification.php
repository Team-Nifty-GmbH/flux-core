<?php

namespace FluxErp\Notifications\QueueMonitor\Job;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Models\QueueMonitor;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\BroadcastNowChannel;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use Ramsey\Uuid\Uuid;

class JobStartedNotification extends Notification implements HasToastNotification
{
    public function __construct(public QueueMonitor $model)
    {
        $this->id = Uuid::uuid5(Uuid::NAMESPACE_URL, $this->model->job_batch_id ?? $this->model->job_id);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->id($this->id)
            ->notifiable($notifiable)
            ->title(__(':job_name started', ['job_name' => __($this->model->getJobName())]))
            ->description($this->model->message)
            ->timeout(0)
            ->progress($this->model->jobBatch?->progress ?? $this->model->progress)
            ->attributes([
                'state' => $this->model->state,
            ]);
    }

    public function via(object $notifiable): array
    {
        return [BroadcastNowChannel::class];
    }
}
