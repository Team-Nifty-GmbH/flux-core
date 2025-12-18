<?php

namespace FluxErp\Events\Task;

use FluxErp\Models\Task;
use FluxErp\Support\Event\SubscribableEvent;
use Illuminate\Queue\SerializesModels;

class TaskReminderEvent extends SubscribableEvent
{
    use SerializesModels;

    public function __construct(public Task $task, public string $type)
    {
        $this->subscribers = collect($this->task->responsibleUser ? [$this->task->responsibleUser] : [])
            ->merge($this->task->users)
            ->unique('id');
    }

    public function eventName(): string
    {
        return '*';
    }
}
