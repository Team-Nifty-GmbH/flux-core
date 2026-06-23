<?php

namespace FluxErp\Support\Mentions;

use FluxErp\Models\User;
use FluxErp\Traits\Model\Mentionable;
use Illuminate\Support\Traits\Macroable;

class MentionableTypesManager
{
    use Macroable;

    /**
     * @var array<string, class-string>|null
     */
    protected ?array $map = null;

    /**
     * @return array<string, class-string>
     */
    public function map(): array
    {
        if ($this->map !== null) {
            return $this->map;
        }

        if (! function_exists('get_models_with_trait')) {
            return [];
        }

        $map = [];
        foreach (get_models_with_trait(Mentionable::class, fn (string $class): string => $class) as $class) {
            $map[$class::mentionTypeKey()] = $class;
        }

        return $this->map = $map;
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
