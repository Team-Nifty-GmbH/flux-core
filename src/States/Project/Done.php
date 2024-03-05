<?php

namespace FluxErp\States\Project;

class Done extends ProjectState
{
    public static $name = 'done';

    public function color(): string
    {
        return static::$color ?? 'positive';
    }
}
