<?php

namespace FluxErp\States\QueueMonitor;

class Stale extends QueueMonitorState
{
    public static $name = 'stale';

    public function color(): string
    {
        return static::$color ?? 'negative';
    }
}
