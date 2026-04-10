<?php

namespace FluxErp\States\Task;

class Canceled extends TaskState
{
    public static bool $isEndState = true;

    public static $name = 'canceled';

    public static int $order = 4;

    public function color(): string
    {
        return static::$color ?? 'red';
    }
}
