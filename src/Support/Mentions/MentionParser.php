<?php

namespace FluxErp\Support\Mentions;

use FluxErp\Enums\MentionTypeEnum;
use FluxErp\Facades\MentionableType;
use FluxErp\Models\User;
use FluxErp\Support\Collection\UserCollection;

class MentionParser
{
    /**
     * @return array<int, array{type: string, user_id: ?int, mentionable_type: ?string, mentionable_id: ?int, raw: string}>
     */
    public function parse(string $text, UserCollection $members): array
    {
        $stripped = preg_replace('/```.*?```/s', '', $text) ?? $text;
        $stripped = preg_replace('/`[^`]*`/', '', $stripped) ?? $stripped;

        $out = [];
        $consumed = [];

        $recordTypes = MentionableType::getRecordMentionableTypes();
        $userTypes = MentionableType::getUserMentionableTypes();
        $userKeys = array_keys($userTypes);
        $userClass = resolve_static(User::class, 'class');
        $userMorphKey = morph_alias(User::class);

        preg_match_all(
            '/(?<!\\\\)(?<![A-Za-z0-9._-])#([a-z][a-z0-9_]*):(\d+)/i',
            $stripped,
            $matches,
            PREG_OFFSET_CAPTURE,
        );
        foreach ($matches[0] as $index => $fullMatch) {
            $key = strtolower($matches[1][$index][0]);
            if (! array_key_exists($key, $recordTypes)) {
                continue;
            }

            $out[] = [
                'type' => MentionTypeEnum::Record,
                'user_id' => null,
                'mentionable_type' => $key,
                'mentionable_id' => (int) $matches[2][$index][0],
                'raw' => $fullMatch[0],
            ];
            $consumed[] = [
                $fullMatch[1],
                $fullMatch[1] + strlen($fullMatch[0]),
            ];
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
            foreach ($matches[0] as $index => $fullMatch) {
                $key = strtolower($matches[1][$index][0]);
                $id = (int) $matches[2][$index][0];

                $out[] = [
                    'type' => MentionTypeEnum::User,
                    'user_id' => ($userTypes[$key] ?? null) === $userClass ? $id : null,
                    'mentionable_type' => $key,
                    'mentionable_id' => $id,
                    'raw' => $fullMatch[0],
                ];
                $consumed[] = [
                    $fullMatch[1],
                    $fullMatch[1] + strlen($fullMatch[0]),
                ];
            }
        }

        preg_match_all(
            '/(?<!\\\\)(?<![A-Za-z0-9._-])@([A-Za-z0-9._-]+)/',
            $stripped,
            $matches,
            PREG_OFFSET_CAPTURE,
        );

        $byFirstname = $members->groupByFirstname()->all();
        $byCode = $members->keyByUserCode()->all();

        foreach ($matches[1] as $index => $tokenMatch) {
            $rawOffset = $matches[0][$index][1];
            if ($this->offsetConsumed($rawOffset, $consumed)) {
                continue;
            }

            $token = strtolower($tokenMatch[0]);
            $raw = '@' . $tokenMatch[0];

            if (isset($byFirstname[$token])) {
                foreach ($byFirstname[$token] as $member) {
                    $out[] = [
                        'type' => MentionTypeEnum::User,
                        'user_id' => (int) $member->id,
                        'mentionable_type' => $userMorphKey,
                        'mentionable_id' => (int) $member->id,
                        'raw' => $raw,
                    ];
                }

                continue;
            }

            if (isset($byCode[$token])) {
                $out[] = [
                    'type' => MentionTypeEnum::User,
                    'user_id' => (int) $byCode[$token]->id,
                    'mentionable_type' => $userMorphKey,
                    'mentionable_id' => (int) $byCode[$token]->id,
                    'raw' => $raw,
                ];
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
}
