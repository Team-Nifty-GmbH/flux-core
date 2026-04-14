<?php

namespace FluxErp\States\Task;

class Open extends TaskState
{
    public static $name = 'open';

    public static int $order = 1;

    public function color(): string
    {
        return static::$color ?? 'neutral';
    }
}
