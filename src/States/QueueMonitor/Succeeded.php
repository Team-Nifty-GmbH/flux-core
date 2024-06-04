<?php

namespace FluxErp\States\QueueMonitor;

class Succeeded extends QueueMonitorState
{
    public static $name = 'succeeded';

    public function color(): string
    {
        return static::$color ?? 'positive';
    }
}
