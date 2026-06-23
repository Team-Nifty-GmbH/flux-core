<?php

namespace FluxErp\Facades;

use FluxErp\Support\Mentions\MentionableTypesManager;
use Illuminate\Support\Facades\Facade;

/**
 * MentionableTypesManager Facade
 *
 * Resolves the models that use the Mentionable trait into a keyed map and
 * exposes the record-mentionable keys (every mentionable type except User).
 *
 * @method static array map()
 * @method static array recordKeys()
 *
 * @see MentionableTypesManager
 */
class MentionableTypes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MentionableTypesManager::class;
    }
}
