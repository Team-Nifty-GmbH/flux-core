<?php

namespace FluxErp\Notifications\QueueMonitor\Batch;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Enums\ToastType;
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
        $this->id = Uuid::uuid5(Uuid::NAMESPACE_URL, $this->model->getKey());
    }

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->toToastNotification($notifiable)->toMail();
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        $failed = $this->model->failed_jobs;
        $total = $this->model->total_jobs;

        return ToastNotification::make()
            ->id($this->id)
            ->notifiable($notifiable)
            ->type(match (true) {
                $failed === 0 => ToastType::SUCCESS,
                $failed === $total => ToastType::ERROR,
                default => ToastType::WARNING,
            })
            ->title(__(':job_name is finished', ['job_name' => __($this->model->name)]))
            ->description(match (true) {
                $failed === 0 => __(':count jobs succeeded', ['count' => $total]),
                $failed === $total => __('All jobs have failed'),
                default => __(':success of :total jobs succeeded, :failed failed', [
                    'success' => $total - $failed,
                    'total' => $total,
                    'failed' => $failed,
                ]),
            })
            ->persistent()
            ->progress($this->model->getProgress())
            ->attributes([
                'progressMeta' => __(':time elapsed', [
                    'time' => $this->model->getElapsedInterval(),
                ]),
            ]);
    }

    public function toWebPush(object $notifiable): ?WebPushMessage
    {
        return $this->toToastNotification($notifiable)->toWebPush();
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
}
