<?php

namespace FluxErp\Traits\Scout;

use FluxErp\Support\Scout\ScoutCustomize;
use Laravel\Scout\Searchable as BaseSearchable;

trait Searchable
{
    use BaseSearchable;

    public static function scoutIndexSettings(): ?array
    {
        return config('scout.' . config('scout.driver') . '.index-settings.' . static::class);
    }

    public function toSearchableArray(): array
    {
        return ScoutCustomize::make($this)->toSearchableArray();
    }
}
