<?php

namespace FluxErp\Traits\Scout;

use Illuminate\Support\Arr;
use Laravel\Scout\Searchable as BaseSearchable;

trait Searchable
{
    use BaseSearchable {
        BaseSearchable::toSearchableArray as toSearchableArrayBase;
    }

    public function toSearchableArray(): array
    {
        return Arr::sortByPattern(
            $this->toSearchableArrayBase(),
            config('scout.sorted_searchable_keys.' . morph_alias(static::class), []),
        );
    }
}
