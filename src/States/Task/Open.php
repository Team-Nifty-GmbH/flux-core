<?php

namespace FluxErp\States\Task;

class Open extends TaskState
{
    public static $name = 'open';

    public function color(): string
    {
        return static::$color ?? 'neutral';
    }
}
