<?php

namespace FluxErp\Support\Mentions;

use FluxErp\Models\User;
use FluxErp\Traits\Model\Mentionable;
use Illuminate\Support\Traits\Macroable;

class MentionableTypesManager
{
    use Macroable;

    /**
     * @return array<string, class-string>
     */
    public function map(): array
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
     * @return array<int, string>
     */
    public function recordKeys(): array
    {
        $userKey = morph_alias(User::class);

        return array_values(array_filter(
            array_keys($this->map()),
            fn (string $key): bool => $key !== $userKey,
        ));
    }
}
