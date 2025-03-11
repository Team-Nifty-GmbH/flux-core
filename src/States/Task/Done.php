<?php

namespace FluxErp\States\Task;

class Done extends TaskState
{
    public static bool $isEndState = true;

    public static $name = 'done';

    public function color(): string
    {
        return static::$color ?? 'emerald';
    }
}
