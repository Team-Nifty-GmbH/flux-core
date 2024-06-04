<?php

namespace FluxErp\States\QueueMonitor;

class Failed extends QueueMonitorState
{
    public static $name = 'failed';

    public function color(): string
    {
        return static::$color ?? 'negative';
    }
}
