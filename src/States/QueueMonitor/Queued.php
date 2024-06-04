<?php

namespace FluxErp\States\QueueMonitor;

class Queued extends QueueMonitorState
{
    public static $name = 'queued';

    public function color(): string
    {
        return static::$color ?? 'neutral';
    }
}
