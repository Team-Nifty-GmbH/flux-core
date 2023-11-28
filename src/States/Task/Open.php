<?php

namespace FluxErp\States\Task;

use FluxErp\Models\StateSetting;

class Open extends TaskState
{
    public static $name = 'open';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'neutral';
    }
}
