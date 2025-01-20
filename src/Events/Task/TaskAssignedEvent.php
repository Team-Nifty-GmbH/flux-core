<?php

namespace FluxErp\Events\Task;

use FluxErp\Models\Task;
use FluxErp\Support\Event\SubscribableEvent;

class TaskAssignedEvent extends SubscribableEvent
{
    public function __construct(public Task $task) {}
}
