<?php

namespace FluxErp\Facades;

use FluxErp\Support\Mentions\MentionableTypeManager;
use Illuminate\Support\Facades\Facade;

/**
 * MentionableTypeManager Facade
 *
 * Manages the models that use the Mentionable trait, split into record-mentionable
 * and user-mentionable types. Types are auto-discovered from the morph map and can
 * be registered or unregistered manually.
 *
 * @method static void autoDiscover()
 * @method static void register(string $class)
 * @method static void unregister(string $key)
 * @method static array all(bool $keysOnly = false)
 * @method static array getRecordMentionableTypes(bool $keysOnly = false)
 * @method static array getUserMentionableTypes(bool $keysOnly = false)
 *
 * @see MentionableTypeManager
 */
class MentionableType extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MentionableTypeManager::class;
    }
}
