<?php

namespace FluxErp\Notifications\Task;

use FluxErp\Events\Task\TaskStartReminderEvent;
use FluxErp\Support\Notification\SubscribableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use NotificationChannels\WebPush\WebPushChannel;

class TaskStartReminderNotification extends SubscribableNotification implements ShouldQueue
{
    use Queueable;

    public static function defaultChannels(?object $notifiable = null): array
    {
        return array_merge(
            [WebPushChannel::class],
            parent::defaultChannels($notifiable)
        );
    }

    public function subscribe(): array
    {
        return [
            resolve_static(TaskStartReminderEvent::class, 'class') => 'sendNotification',
        ];
    }

    protected function getDescription(): ?string
    {
        return $this->model->getLabel();
    }

    protected function getModelFromEvent(object $event): ?Model
    {
        return $event->task;
    }

    protected function getNotificationIcon(): ?string
    {
        return 'play';
    }

    protected function getSubscriptionsForEvent(object $event): Collection
    {
        return parent::getSubscriptionsForEvent($event)
            ->intersect($event->getSubscribers());
    }

    protected function getTitle(): string
    {
        return __('Task :name is starting soon', ['name' => $this->model->name]);
    }
}
