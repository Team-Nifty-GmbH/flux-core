<?php

namespace FluxErp\States\ProjectTask;

use FluxErp\Models\StateSetting;

class InProgress extends ProjectTaskState
{
    public static $name = 'in_progress';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'warning';
    }
}
