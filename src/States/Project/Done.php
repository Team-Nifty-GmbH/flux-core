<?php

namespace FluxErp\States\Project;

class Done extends ProjectState
{
    public static bool $isEndState = true;

    public static $name = 'done';

    public function color(): string
    {
        return static::$color ?? 'emerald';
    }
}
