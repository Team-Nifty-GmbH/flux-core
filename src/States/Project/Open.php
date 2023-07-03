<?php

namespace FluxErp\States\Project;

use FluxErp\Models\StateSetting;

class Open extends ProjectState
{
    public static $name = 'open';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'neutral';
    }
}
