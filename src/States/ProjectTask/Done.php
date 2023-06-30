<?php

namespace FluxErp\States\ProjectTask;

use FluxErp\Models\StateSetting;

class Done extends ProjectTaskState
{
    public static $name = 'done';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'positive';
    }
}
