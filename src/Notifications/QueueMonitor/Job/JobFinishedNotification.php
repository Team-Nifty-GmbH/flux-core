<?php

namespace FluxErp\Notifications\QueueMonitor\Job;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Models\QueueMonitor;
use FluxErp\Notifications\Notification;
use FluxErp\States\QueueMonitor\Succeeded;
use FluxErp\Support\Notification\BroadcastNowChannel;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\WebPush\WebPushMessage;

class JobFinishedNotification extends Notification implements HasToastNotification
{
    public function __construct(public QueueMonitor $model)
    {
        $this->id = $this->model->job_batch_id ?? $this->model->job_id;
    }

    public function via(object $notifiable): array
    {
        $via = [BroadcastNowChannel::class, DatabaseChannel::class];
        if ($this->model
            ->queueMonitorables()
            ->where('queue_monitorable_type', Relation::getMorphClassAlias($notifiable::class))
            ->where('queue_monitorable_id', $notifiable->id)
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
            ->title(__(':job-name is finished', ['job-name' => __($this->model->getJobName())]))
            ->description(
                __(':time elapsed', ['time' => $this->model->getElapsedInterval()]) .
                ($this->model->message ? '<br>' . $this->model->message : '')
            )
            ->icon(
                is_a($this->model->state, Succeeded::class, true)
                    ? 'success'
                    : 'error'
            )
            ->timeout(0)
            ->accept(unserialize($this->model->accept) ?: null)
            ->reject(unserialize($this->model->reject) ?: null)
            ->attributes([
                'progress' => $this->model->jobBatch?->progress ?? $this->model->progress,
                'state' => $this->model->state,
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
        return $this->toToastNotification($notifiable)->toWebPush($notifiable);
    }
}
