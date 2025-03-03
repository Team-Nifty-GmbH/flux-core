<?php

namespace FluxErp\States\QueueMonitor;

class Running extends QueueMonitorState
{
    public static $name = 'running';

    public function color(): string
    {
        return static::$color ?? 'amber';
    }
}
