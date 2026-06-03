<?php

namespace FluxErp\Services\Mentions;

use FluxErp\Models\User;
use Illuminate\Support\Collection;
use Throwable;

class MentionRenderer
{
    public function tokensToHtml(string $text, ?Collection $members = null): string
    {
        $text = $this->renderRecordTokens($text);
        $text = $this->renderExplicitUserTokens($text);
        $text = $this->renderMemberTokens($text, $members ?? collect());

        return $text;
    }

    private function renderRecordTokens(string $text): string
    {
        $types = MentionableTypes::map();
        $userKey = morph_alias(User::class);
        $pattern = '/(?<!\\\\)(?<![A-Za-z0-9._-])#([a-z][a-z0-9_]*):(\d+)/i';

        if (! preg_match_all($pattern, $text, $matches)) {
            return $text;
        }

        $idsByKey = [];
        foreach ($matches[1] as $i => $rawKey) {
            $key = strtolower($rawKey);
            if ($key !== $userKey && isset($types[$key])) {
                $idsByKey[$key][] = (int) $matches[2][$i];
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
            $pattern,
            function (array $m) use ($types, $userKey, $recordsByKey): string {
                $key = strtolower($m[1]);
                $id = (int) $m[2];
                if ($key === $userKey || ! isset($types[$key])) {
                    return $m[0];
                }

                $record = $recordsByKey[$key][$id] ?? null;
                if ($record === null) {
                    return '<span class="mention mention--missing">' . e(__('@deleted entry')) . '</span>';
                }

                $label = e($record->getMentionLabel());
                $typeLabel = e($types[$key]::mentionTypeLabel());
                $stateAttrs = $record->getMentionState()?->toPillAttributes() ?? '';

                try {
                    $url = $record->getMentionUrl();
                } catch (Throwable) {
                    return sprintf(
                        '<span class="mention mention--%s" data-mention-type="%s"%s>%s</span>',
                        e($key),
                        $typeLabel,
                        $stateAttrs,
                        $label,
                    );
                }

                return sprintf(
                    '<a class="mention mention--%s" href="%s" data-mention="%s:%d" data-mention-type="%s"%s>%s</a>',
                    e($key),
                    e($url),
                    e($key),
                    $id,
                    $typeLabel,
                    $stateAttrs,
                    $label,
                );
            },
            $text,
        ) ?? $text;
    }

    private function renderExplicitUserTokens(string $text): string
    {
        $userKey = morph_alias(User::class);
        $pattern = '/(?<!\\\\)(?<![A-Za-z0-9._-])@' . preg_quote($userKey, '/') . ':(\d+)/i';

        if (! preg_match_all($pattern, $text, $matches)) {
            return $text;
        }

        $users = User::query()
            ->whereKey(array_unique(array_map('intval', $matches[1])))
            ->get()
            ->keyBy(fn ($user) => $user->getKey());

        return preg_replace_callback(
            $pattern,
            function (array $m) use ($users): string {
                $id = (int) $m[1];
                $user = $users[$id] ?? null;
                if ($user === null) {
                    return '<span class="mention mention--missing">' . e(__('@deleted entry')) . '</span>';
                }

                return sprintf(
                    '<a class="mention mention--user" href="%s" data-mention="user:%d" data-user-id="%d">%s</a>',
                    e($user->getMentionUrl()),
                    $id,
                    $id,
                    e($user->getMentionLabel()),
                );
            },
            $text,
        ) ?? $text;
    }

    private function renderMemberTokens(string $text, Collection $members): string
    {
        if ($members->isEmpty()) {
            return $text;
        }

        $byFirstname = $members->keyBy(fn ($u) => strtolower((string) ($u->firstname ?? '')))->all();
        $byCode = $members->keyBy(fn ($u) => strtolower((string) ($u->user_code ?? '')))->all();

        return preg_replace_callback(
            '/(?<!\\\\)(?<![A-Za-z0-9._-])@([A-Za-z0-9._-]+)/',
            function (array $m) use ($byFirstname, $byCode): string {
                $token = strtolower($m[1]);
                $user = $byFirstname[$token] ?? $byCode[$token] ?? null;
                if ($user === null) {
                    return $m[0];
                }

                $id = (int) $user->getKey();

                return sprintf(
                    '<a class="mention mention--user" href="%s" data-mention="user:%d" data-user-id="%d">%s</a>',
                    e($user->getMentionUrl()),
                    $id,
                    $id,
                    e($user->getMentionLabel()),
                );
            },
            $text,
        ) ?? $text;
    }
}
