<?php

namespace FluxErp\Traits\Scout;

use Illuminate\Support\Arr;
use Laravel\Scout\Searchable as BaseSearchable;

trait Searchable
{
    use BaseSearchable {
        BaseSearchable::toSearchableArray as toSearchableArrayBase;
    }

    public static function scoutIndexSettings(): array
    {
        return config(
            'scout.' . config('scout.driver') . '.index-settings.' . static::class,
            []
        );
    }

    public function toSearchableArray(): array
    {
        return Arr::sortByPattern(
            $this->toSearchableArrayBase(),
            config('scout.sorted_searchable_keys.' . static::class, []),
        );
    }
}
