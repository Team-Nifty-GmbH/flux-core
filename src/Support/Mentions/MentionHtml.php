<?php

namespace FluxErp\Support\Mentions;

use FluxErp\Facades\MentionableType;

class MentionHtml
{
    public static function toTokens(string $html): string
    {
        $userKeys = MentionableType::getUserMentionableTypes(keysOnly: true);

        return preg_replace_callback(
            '/<(?:span|a)\b[^>]*\bdata-id="([a-z][a-z0-9_]*):(\d+)"[^>]*>.*?<\/(?:span|a)>/i',
            function (array $matches) use ($userKeys): string {
                $key = strtolower($matches[1]);
                $sigil = in_array($key, $userKeys, true) ? '@' : '#';

                return $sigil . $key . ':' . $matches[2];
            },
            $html,
        ) ?? $html;
    }
}
