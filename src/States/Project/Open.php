<?php

namespace FluxErp\States\Project;

class Open extends ProjectState
{
    public static $name = 'open';

    public function color(): string
    {
        return static::$color ?? 'neutral';
    }
}
