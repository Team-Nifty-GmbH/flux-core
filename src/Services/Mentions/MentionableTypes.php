<?php

namespace FluxErp\Services\Mentions;

use FluxErp\Models\User;
use FluxErp\Traits\Model\Mentionable;

class MentionableTypes
{
    /**
     * @return array<string, class-string>
     */
    public static function map(): array
    {
        if (! function_exists('get_models_with_trait')) {
            return [];
        }

        $map = [];
        foreach (get_models_with_trait(Mentionable::class, fn (string $class): string => $class) as $class) {
            $map[$class::mentionTypeKey()] = $class;
        }

        return $map;
    }

    /**
     * Type keys for `#` record mentions (every mentionable type except the user/people type).
     *
     * @return array<int, string>
     */
    public static function recordKeys(): array
    {
        $userKey = morph_alias(User::class);

        return array_values(array_filter(
            array_keys(static::map()),
            fn (string $key): bool => $key !== $userKey,
        ));
    }
}
