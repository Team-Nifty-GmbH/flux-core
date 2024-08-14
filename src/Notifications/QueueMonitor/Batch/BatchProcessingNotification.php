<?php

namespace FluxErp\Notifications\QueueMonitor\Batch;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Models\JobBatch;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\BroadcastNowChannel;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;

class BatchProcessingNotification extends Notification implements HasToastNotification
{
    public function __construct(public JobBatch $model)
    {
        $this->id = $this->model->id;
    }

    public function via(object $notifiable): array
    {
        return [BroadcastNowChannel::class];
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->notifiable($notifiable)
            ->title(__(':job_name is processing', ['job_name' => __($this->model->name)]))
            ->description($this->model->getProcessedJobs().' / '.$this->model->total_jobs.'<br>'
                .__(':time remaining', ['time' => $this->model->getRemainingInterval()])
            )
            ->icon('info')
            ->timeout(0)
            ->attributes([
                'progress' => $this->model->getProgress(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }
}
