<?php

namespace FluxErp\States\Project;

use FluxErp\Models\StateSetting;

class Done extends ProjectState
{
    public static $name = 'done';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'positive';
    }
}
