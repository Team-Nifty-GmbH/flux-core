<?php

namespace FluxErp\Support\Mentions;

use FluxErp\Facades\MentionableType;

class MentionPillRefresher
{
    protected const PILL = '/<(a|span)\b([^>]*\bdata-id="([a-z][a-z0-9_]*):(\d+)"[^>]*)>/i';

    public function refresh(string $html): string
    {
        $userKeys = MentionableType::getUserMentionableTypes(keysOnly: true);
        $types = MentionableType::all();

        if (! preg_match_all(static::PILL, $html, $matches, PREG_SET_ORDER)) {
            return $html;
        }

        $idsByKey = [];
        foreach ($matches as $match) {
            $key = strtolower($match[3]);
            if (! in_array($key, $userKeys, true) && array_key_exists($key, $types)) {
                $idsByKey[$key][] = (int) $match[4];
            }
        }

        $recordsByKey = [];
        foreach ($idsByKey as $key => $ids) {
            $recordsByKey[$key] = resolve_static($types[$key], 'query')
                ->whereKey($ids)
                ->get()
                ->keyBy(fn ($record) => $record->getKey());
        }

        return preg_replace_callback(
            static::PILL,
            function (array $match) use ($userKeys, $types, $recordsByKey): string {
                $tag = $match[1];
                $attributes = $this->stripStateAttributes($match[2]);
                $key = strtolower($match[3]);
                $id = (int) $match[4];

                if (in_array($key, $userKeys, true) || ! array_key_exists($key, $types)) {
                    return $match[0];
                }

                $state = ($recordsByKey[$key][$id] ?? null)?->getMentionState();
                $stateAttributes = $state?->toPillAttributes() ?? '';

                return '<' . $tag . $attributes . $stateAttributes . '>';
            },
            $html,
        ) ?? $html;
    }

    protected function stripStateAttributes(string $attributes): string
    {
        if (preg_match('/\bdata-mention-state="/i', $attributes) !== 1) {
            return $attributes;
        }

        $attributes = preg_replace('/\s+data-mention-state="[^"]*"/i', '', $attributes);
        $attributes = preg_replace('/\s+title="[^"]*"/i', '', $attributes);
        $attributes = preg_replace('/\s+style="[^"]*--mention-state-color[^"]*"/i', '', $attributes);

        return $attributes;
    }
}
