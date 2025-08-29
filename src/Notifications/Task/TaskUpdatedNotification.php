<?php

namespace FluxErp\Notifications\Task;

use FluxErp\Models\Task;
use FluxErp\Support\Notification\SubscribableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskUpdatedNotification extends SubscribableNotification implements ShouldQueue
{
    use Queueable;

    public function subscribe(): array
    {
        return [
            'eloquent.updated: ' . resolve_static(Task::class, 'class') => 'sendNotification',
        ];
    }

    protected function getDescription(): ?string
    {
        return $this->model->getLabel();
    }

    protected function getNotificationIcon(): ?string
    {
        return 'clipboard-list';
    }

    protected function getTitle(): string
    {
        return __(
            ':username updated a task',
            [
                'username' => $this->model->getUpdatedBy()?->getLabel() ?? __('Unknown'),
            ],
        );
    }
}
