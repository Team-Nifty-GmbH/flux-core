<?php

namespace FluxErp\States\Task;

class Done extends TaskState
{
    public static $name = 'done';

    public static bool $isEndState = true;

    public function color(): string
    {
        return static::$color ?? 'positive';
    }
}
