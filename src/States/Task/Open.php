<?php

namespace FluxErp\States\Task;

class Open extends TaskState
{
    public static bool $isEndState = false;

    public static $name = 'open';

    public function color(): string
    {
        return static::$color ?? 'neutral';
    }
}
