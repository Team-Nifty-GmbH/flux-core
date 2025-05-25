<?php

namespace FluxErp\Traits\Scout;

use FluxErp\Support\Scout\ScoutCustomize;

trait Searchable
{
    public static function scoutIndexSettings(): array
    {
        return config(
            'scout.' . config('scout.driver') . '.index-settings.' . static::class,
            []
        );
    }

    public function toSearchableArray(): array
    {
        return ScoutCustomize::make($this)->toSearchableArray();
    }
}
