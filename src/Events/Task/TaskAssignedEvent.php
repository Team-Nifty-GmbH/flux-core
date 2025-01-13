<?php

namespace FluxErp\Events\Task;

use FluxErp\Models\Task;

class TaskAssignedEvent
{
    public function __construct(public Task $task) {}

    public function broadcastChannel(): string
    {
        return $this->task->broadcastChannel();
    }
}
