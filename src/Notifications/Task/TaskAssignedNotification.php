<?php

namespace FluxErp\Notifications\Task;

use FluxErp\Events\Task\TaskAssignedEvent;
use FluxErp\Support\Notification\SubscribableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TaskAssignedNotification extends SubscribableNotification implements ShouldQueue
{
    use Queueable;

    public function subscribe(): array
    {
        return [
            TaskAssignedEvent::class => 'sendNotification',
        ];
    }

    protected function getModelFromEvent(object $event): ?Model
    {
        return $event->task;
    }

    protected function getTitle(): string
    {
        return __(
            ':username assigned you a task',
            [
                'username' => auth()->user()?->getLabel() ?? __('Unknown'),
            ],
        );
    }

    protected function getDescription(): ?string
    {
        return $this->model->name;
    }

    protected function getNotificationIcon(): ?string
    {
        return 'clipboard-list';
    }

    protected function getSubscriptionsForEvent(object $event): Collection
    {
        return parent::getSubscriptionsForEvent($event)
            ->intersect($event->getSubscribers());
    }
}
