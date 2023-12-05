<?php

namespace FluxErp\States\Task;

use FluxErp\Models\StateSetting;

class Canceled extends TaskState
{
    public static $name = 'canceled';

    public static bool $isEndState = true;

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'negative';
    }
}
