<?php

namespace FluxErp\Notifications\QueueMonitor\Batch;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Models\JobBatch;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\BroadcastNowChannel;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\WebPush\WebPushMessage;
use Ramsey\Uuid\Uuid;

class BatchFinishedNotification extends Notification implements HasToastNotification
{
    public function __construct(public JobBatch $model)
    {
        $this->id = Uuid::uuid5(Uuid::NAMESPACE_URL, static::class . ':' . $this->model->getKey());
    }

    public function via(object $notifiable): array
    {
        $via = [BroadcastNowChannel::class, DatabaseChannel::class];
        if ($this->model
            ->jobBatchables()
            ->where('job_batchable_type', morph_alias($notifiable::class))
            ->where('job_batchable_id', $notifiable->id)
            ->where('notify_on_finish', true)
            ->exists()
        ) {
            $via[] = MailChannel::class;
        }

        return $via;
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->notifiable($notifiable)
            ->title(__(':job_name is finished', ['job_name' => __($this->model->name)]))
            ->description(
                $this->model->failed_jobs === 0
                    ? __('All jobs have been processed successfully')
                    : ($this->model->failed_jobs === $this->model->total_jobs
                        ? __('All jobs have failed')
                        : __(':count jobs have failed', ['count' => $this->model->failed_jobs])
                    )
            )
            ->icon($this->model->failed_jobs === 0
                ? 'success'
                : ($this->model->failed_jobs === $this->model->total_jobs ? 'error' : 'warning')
            )
            ->timeout(0)
            ->attributes([
                'progress' => $this->model->getProgress(),
            ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->toToastNotification($notifiable)->toMail();
    }

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }

    public function toWebPush(object $notifiable): ?WebPushMessage
    {
        return $this->toToastNotification($notifiable)->toWebPush();
    }
}
