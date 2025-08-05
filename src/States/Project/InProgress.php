<?php

namespace FluxErp\States\Project;

class InProgress extends ProjectState
{
    public static $name = 'in_progress';

    public function color(): string
    {
        return static::$color ?? 'amber';
    }
}
