<?php

namespace FluxErp\Notifications\Task;

use FluxErp\Events\Task\TaskReminderEvent;
use FluxErp\Support\Notification\SubscribableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use NotificationChannels\WebPush\WebPushChannel;

class TaskReminderNotification extends SubscribableNotification implements ShouldQueue
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
            resolve_static(TaskReminderEvent::class, 'class') => 'sendNotification',
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
        return $this->event->type === 'start'
            ? 'play'
            : 'clock';
    }

    protected function getSubscriptionsForEvent(object $event): Collection
    {
        return parent::getSubscriptionsForEvent($event)
            ->intersect($event->getSubscribers());
    }

    protected function getTitle(): string
    {
        return $this->event->type === 'start'
            ? __('Task :name is starting soon', ['name' => $this->getDescription() ?? __('Unknown')])
            : __('Task :name is due soon', ['name' => $this->getDescription() ?? __('Unknown')]);
    }
}
