<?php

namespace FluxErp\Services\Mentions;

use FluxErp\Models\User;

class MentionHtml
{
    public static function toTokens(string $html): string
    {
        $userKey = morph_alias(User::class);

        return preg_replace_callback(
            '/<span\b[^>]*\bdata-id="([a-z][a-z0-9_]*):(\d+)"[^>]*>.*?<\/span>/i',
            function (array $m) use ($userKey): string {
                $key = strtolower($m[1]);
                $sigil = $key === $userKey ? '@' : '#';

                return $sigil . $key . ':' . $m[2];
            },
            $html,
        ) ?? $html;
    }
}
