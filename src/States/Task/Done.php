<?php

namespace FluxErp\States\Task;

class Done extends TaskState
{
    public static bool $isEndState = true;

    public static $name = 'done';

    public static int $order = 3;

    public function color(): string
    {
        return static::$color ?? 'emerald';
    }
}
