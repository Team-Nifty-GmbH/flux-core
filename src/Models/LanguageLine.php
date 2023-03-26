<?php

namespace FluxErp\Models;

use Laravel\Scout\Searchable;
use Spatie\TranslationLoader\LanguageLine as SpatieLanguageLine;

class LanguageLine extends SpatieLanguageLine
{
    use Searchable;
}
