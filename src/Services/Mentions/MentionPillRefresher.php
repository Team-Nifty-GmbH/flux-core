<?php

namespace FluxErp\Services\Mentions;

use FluxErp\Models\User;

class MentionPillRefresher
{
    protected const PILL = '/<(a|span)\b([^>]*\bdata-id="([a-z][a-z0-9_]*):(\d+)"[^>]*)>/i';

    public function refresh(string $html): string
    {
        $userKey = morph_alias(User::class);
        $types = MentionableTypes::map();

        if (! preg_match_all(self::PILL, $html, $matches, PREG_SET_ORDER)) {
            return $html;
        }

        $idsByKey = [];
        foreach ($matches as $m) {
            $key = strtolower($m[3]);
            if ($key !== $userKey && isset($types[$key])) {
                $idsByKey[$key][] = (int) $m[4];
            }
        }

        $recordsByKey = [];
        foreach ($idsByKey as $key => $ids) {
            $recordsByKey[$key] = $types[$key]::query()
                ->whereKey(array_unique($ids))
                ->get()
                ->keyBy(fn ($record) => $record->getKey());
        }

        return preg_replace_callback(
            self::PILL,
            function (array $m) use ($userKey, $types, $recordsByKey): string {
                $tag = $m[1];
                $attrs = $this->stripStateAttributes($m[2]);
                $key = strtolower($m[3]);
                $id = (int) $m[4];

                if ($key === $userKey || ! isset($types[$key])) {
                    return $m[0];
                }

                $state = ($recordsByKey[$key][$id] ?? null)?->getMentionState();
                $stateAttrs = $state?->toPillAttributes() ?? '';

                return '<' . $tag . $attrs . $stateAttrs . '>';
            },
            $html,
        ) ?? $html;
    }

    private function stripStateAttributes(string $attrs): string
    {
        $attrs = preg_replace('/\s+data-mention-state="[^"]*"/i', '', $attrs);
        $attrs = preg_replace('/\s+title="[^"]*"/i', '', $attrs);
        $attrs = preg_replace('/\s+style="[^"]*"/i', '', $attrs);

        return $attrs;
    }
}
