<?php

namespace FluxErp\Traits;

use FluxErp\Models\Tag;
use Spatie\Tags\HasTags as BaseHasTags;

trait HasTags
{
    use BaseHasTags;

    public static function getTagClassName(): string
    {
        return resolve_static(Tag::class, 'class');
    }
}
