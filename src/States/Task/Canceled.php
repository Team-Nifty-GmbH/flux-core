<?php

namespace FluxErp\States\Task;

class Canceled extends TaskState
{
    public static $name = 'canceled';

    public static bool $isEndState = true;

    public function color(): string
    {
        return static::$color ?? 'negative';
    }
}
