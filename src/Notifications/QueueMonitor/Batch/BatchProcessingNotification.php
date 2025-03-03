<?php

namespace FluxErp\Notifications\QueueMonitor\Batch;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Models\JobBatch;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\BroadcastNowChannel;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use Ramsey\Uuid\Uuid;

class BatchProcessingNotification extends Notification implements HasToastNotification
{
    public function __construct(public JobBatch $model)
    {
        $this->id = Uuid::uuid5(Uuid::NAMESPACE_URL, $this->model->getKey());
    }

    public function via(object $notifiable): array
    {
        return [BroadcastNowChannel::class];
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->id($this->id)
            ->notifiable($notifiable)
            ->title(__(':job_name is processing', ['job_name' => __($this->model->name)]))
            ->description($this->model->getProcessedJobs() . ' / ' . $this->model->total_jobs . '<br>'
                . __(':time remaining', ['time' => $this->model->getRemainingInterval()])
            )
            ->persistent()
            ->progress($this->model->getProgress());
    }

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }
}
