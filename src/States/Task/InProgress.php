<?php

namespace FluxErp\States\Task;

use FluxErp\Models\StateSetting;

class InProgress extends TaskState
{
    public static $name = 'in_progress';

    public static bool $isEndState = false;

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'warning';
    }
}
