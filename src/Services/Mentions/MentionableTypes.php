<?php

namespace FluxErp\Services\Mentions;

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
}
