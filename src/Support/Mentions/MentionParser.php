<?php

namespace FluxErp\Support\Mentions;

use FluxErp\Enums\MentionTypeEnum;
use FluxErp\Facades\MentionableType;
use FluxErp\Models\User;
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
        $userTypes = MentionableType::getUserMentionableTypes();
        $userKeys = array_keys($userTypes);

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
            foreach ($matches[0] as $index => $fullMatch) {
                $key = strtolower($matches[1][$index][0]);
                $id = (int) $matches[2][$index][0];

                $out[] = [
                    'type' => MentionTypeEnum::User,
                    'user_id' => ($userTypes[$key] ?? null) === User::class ? $id : null,
                    'mentionable_type' => $key,
                    'mentionable_id' => $id,
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

        $byFirstname = $members->groupBy(fn ($member) => strtolower((string) ($member->firstname ?? '')))->all();
        $byCode = $members->keyBy(fn ($member) => strtolower((string) ($member->user_code ?? '')))->all();

        foreach ($matches[1] as $index => $tokenMatch) {
            $rawOffset = $matches[0][$index][1];
            if ($this->offsetConsumed($rawOffset, $consumed)) {
                continue;
            }

            $token = strtolower($tokenMatch[0]);
            $raw = '@' . $tokenMatch[0];

            if (isset($byFirstname[$token])) {
                foreach ($byFirstname[$token] as $member) {
                    $out[] = $this->plain(MentionTypeEnum::User, (int) $member->id, $raw);
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
            'mentionable_type' => $type === MentionTypeEnum::User ? morph_alias(User::class) : null,
            'mentionable_id' => $userId,
            'raw' => $raw,
        ];
    }
}
