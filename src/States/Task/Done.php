<?php

namespace FluxErp\States\Task;

use FluxErp\Models\StateSetting;

class Done extends TaskState
{
    public static $name = 'done';

    public static bool $isEndState = true;

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'positive';
    }
}