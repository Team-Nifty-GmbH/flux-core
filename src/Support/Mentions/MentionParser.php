<?php

namespace FluxErp\Support\Mentions;

use FluxErp\Enums\MentionTypeEnum;
use FluxErp\Facades\MentionableType;
use Illuminate\Support\Collection;

class MentionParser
{
    /**
     * @param  Collection<int, object>  $members
     * @return array<int, array{type: string, user_id: ?int, mentionable_type: ?string, mentionable_id: ?int, raw: string}>
     */
    public function parse(string $text, Collection $members): array
    {
        $stripped = preg_replace('/```.*?```/s', '', $text) ?? $text;
        $stripped = preg_replace('/`[^`]*`/', '', $stripped) ?? $stripped;

        $out = [];
        $consumed = [];

        $recordTypes = MentionableType::getRecordMentionableTypes();
        $userKeys = MentionableType::getUserMentionableTypes(keysOnly: true);

        preg_match_all(
            '/(?<!\\\\)(?<![A-Za-z0-9._-])#([a-z][a-z0-9_]*):(\d+)/i',
            $stripped,
            $matches,
            PREG_OFFSET_CAPTURE,
        );
        foreach ($matches[0] as $i => $fullMatch) {
            $key = strtolower($matches[1][$i][0]);
            if (! array_key_exists($key, $recordTypes)) {
                continue;
            }

            $out[] = [
                'type' => MentionTypeEnum::Record,
                'user_id' => null,
                'mentionable_type' => $key,
                'mentionable_id' => (int) $matches[2][$i][0],
                'raw' => $fullMatch[0],
            ];
            $consumed[] = [$fullMatch[1], $fullMatch[1] + strlen($fullMatch[0])];
        }

        if ($userKeys !== []) {
            $userAlternation = implode(
                '|',
                array_map(fn (string $key): string => preg_quote($key, '/'), $userKeys),
            );

            preg_match_all(
                '/(?<!\\\\)(?<![A-Za-z0-9._-])@(' . $userAlternation . '):(\d+)/i',
                $stripped,
                $matches,
                PREG_OFFSET_CAPTURE,
            );
            foreach ($matches[0] as $i => $fullMatch) {
                $out[] = [
                    'type' => MentionTypeEnum::User,
                    'user_id' => (int) $matches[2][$i][0],
                    'mentionable_type' => null,
                    'mentionable_id' => null,
                    'raw' => $fullMatch[0],
                ];
                $consumed[] = [$fullMatch[1], $fullMatch[1] + strlen($fullMatch[0])];
            }
        }

        preg_match_all(
            '/(?<!\\\\)(?<![A-Za-z0-9._-])@([A-Za-z0-9._-]+)/',
            $stripped,
            $matches,
            PREG_OFFSET_CAPTURE,
        );

        $byFirstname = $members->groupBy(fn ($u) => strtolower((string) ($u->firstname ?? '')))->all();
        $byCode = $members->keyBy(fn ($u) => strtolower((string) ($u->user_code ?? '')))->all();

        foreach ($matches[1] as $i => $tokenMatch) {
            $rawOffset = $matches[0][$i][1];
            if ($this->offsetConsumed($rawOffset, $consumed)) {
                continue;
            }

            $token = strtolower($tokenMatch[0]);
            $raw = '@' . $tokenMatch[0];

            if (isset($byFirstname[$token])) {
                foreach ($byFirstname[$token] as $u) {
                    $out[] = $this->plain(MentionTypeEnum::User, (int) $u->id, $raw);
                }

                continue;
            }

            if (isset($byCode[$token])) {
                $out[] = $this->plain(MentionTypeEnum::User, (int) $byCode[$token]->id, $raw);
            }
        }

        return $out;
    }

    /**
     * @param  array<int, array{0: int, 1: int}>  $consumed
     */
    protected function offsetConsumed(int $offset, array $consumed): bool
    {
        foreach ($consumed as [$start, $end]) {
            if ($offset >= $start && $offset < $end) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{type: string, user_id: ?int, mentionable_type: ?string, mentionable_id: ?int, raw: string}
     */
    protected function plain(string $type, ?int $userId, string $raw): array
    {
        return [
            'type' => $type,
            'user_id' => $userId,
            'mentionable_type' => null,
            'mentionable_id' => null,
            'raw' => $raw,
        ];
    }
}
