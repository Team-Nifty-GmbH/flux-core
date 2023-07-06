<?php

namespace FluxErp\States\ProjectTask;

use FluxErp\Models\StateSetting;

class Open extends ProjectTaskState
{
    public static $name = 'open';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'neutral';
    }
}
