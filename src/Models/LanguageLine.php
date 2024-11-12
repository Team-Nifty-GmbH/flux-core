<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use FluxErp\Traits\Scout\Searchable;
use Spatie\TranslationLoader\LanguageLine as SpatieLanguageLine;

class LanguageLine extends SpatieLanguageLine
{
    use CacheModelQueries, ResolvesRelationsThroughContainer, Searchable;
}
