<?php

namespace FluxErp\States\Task;

class InProgress extends TaskState
{
    public static $name = 'in_progress';

    public function color(): string
    {
        return static::$color ?? 'amber';
    }
}
