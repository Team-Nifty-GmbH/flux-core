<?php

namespace FluxErp\Events\Task;

use FluxErp\Models\Task;
use FluxErp\Support\Event\SubscribableEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class TaskStartReminderEvent extends SubscribableEvent
{
    use SerializesModels;

    public function __construct(public Task $task) {}

    public function eventName(): string
    {
        return '*';
    }

    public function getSubscribers(): ?Collection
    {
        return collect($this->task->responsibleUser ? [$this->task->responsibleUser] : [])
            ->merge($this->task->users)
            ->unique('id');
    }
}
