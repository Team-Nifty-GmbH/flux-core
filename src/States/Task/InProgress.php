<?php

namespace FluxErp\States\Task;

class InProgress extends TaskState
{
    public static $name = 'in_progress';

    public static bool $isEndState = false;

    public function color(): string
    {
        return static::$color ?? 'warning';
    }
}
