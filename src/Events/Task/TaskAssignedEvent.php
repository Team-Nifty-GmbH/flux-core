<?php

namespace FluxErp\Events\Task;

use FluxErp\Models\Task;
use FluxErp\Support\Event\SubscribableEvent;
use Illuminate\Queue\SerializesModels;

class TaskAssignedEvent extends SubscribableEvent
{
    use SerializesModels;

    public function __construct(public Task $task) {}

    public function eventName(): string
    {
        return '*';
    }
}
