<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use Laravel\Scout\Searchable;
use Spatie\TranslationLoader\LanguageLine as SpatieLanguageLine;

class LanguageLine extends SpatieLanguageLine
{
    use CacheModelQueries, Searchable;
}
