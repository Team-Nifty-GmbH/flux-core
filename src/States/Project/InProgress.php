<?php

namespace FluxErp\States\Project;

use FluxErp\Models\StateSetting;

class InProgress extends ProjectState
{
    public static $name = 'in_progress';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'warning';
    }
}
