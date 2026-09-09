<?php

namespace FluxErp\Support\Mentions;

use FluxErp\Facades\MentionableType;
use FluxErp\Models\User;
use FluxErp\Support\Collection\UserCollection;
use Illuminate\Database\Eloquent\Model;

class MentionRenderer
{
    public function tokensToHtml(string $text, ?UserCollection $members = null): string
    {
        $text = $this->renderRecordTokens($text);
        $text = $this->renderExplicitUserTokens($text);
        $text = $this->renderMemberTokens($text, $members ?? app(UserCollection::class));

        return $text;
    }

    protected function renderRecordTokens(string $text): string
    {
        $types = MentionableType::getRecordMentionableTypes();
        $pattern = '/(?<!\\\\)(?<![A-Za-z0-9._-])#([a-z][a-z0-9_]*):(\d+)/i';

        if (! preg_match_all($pattern, $text, $matches)) {
            return $text;
        }

        $idsByKey = [];
        foreach ($matches[1] as $index => $rawKey) {
            $key = strtolower($rawKey);
            if (array_key_exists($key, $types)) {
                $idsByKey[$key][] = (int) $matches[2][$index];
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
            $pattern,
            function (array $matches) use ($types, $recordsByKey): string {
                $key = strtolower($matches[1]);
                $id = (int) $matches[2];
                if (! array_key_exists($key, $types)) {
                    return $matches[0];
                }

                $record = $recordsByKey[$key][$id] ?? null;
                if (is_null($record)) {
                    return '<span class="mention mention--missing">' . e(__('@deleted entry')) . '</span>';
                }

                $label = e($record->getMentionLabel());
                $typeLabel = e(resolve_static($types[$key], 'mentionTypeLabel'));
                $stateAttrs = $record->getMentionState()?->toPillAttributes() ?? '';
                $url = $record->getMentionUrl();

                if (is_null($url)) {
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

    protected function renderExplicitUserTokens(string $text): string
    {
        $userTypes = MentionableType::getUserMentionableTypes();
        if ($userTypes === []) {
            return $text;
        }

        $userAlternation = implode(
            '|',
            array_map(fn (string $key): string => preg_quote($key, '/'), array_keys($userTypes)),
        );
        $pattern = '/(?<!\\\\)(?<![A-Za-z0-9._-])@(' . $userAlternation . '):(\d+)/i';

        if (! preg_match_all($pattern, $text, $matches)) {
            return $text;
        }

        $idsByKey = [];
        foreach ($matches[1] as $index => $rawKey) {
            $idsByKey[strtolower($rawKey)][] = (int) $matches[2][$index];
        }

        $usersByKey = [];
        foreach ($idsByKey as $key => $ids) {
            $usersByKey[$key] = resolve_static($userTypes[$key], 'query')
                ->whereKey($ids)
                ->get()
                ->keyBy(fn ($user) => $user->getKey());
        }

        return preg_replace_callback(
            $pattern,
            function (array $matches) use ($usersByKey): string {
                $key = strtolower($matches[1]);
                $user = $usersByKey[$key][(int) $matches[2]] ?? null;
                if (is_null($user)) {
                    return '<span class="mention mention--missing">' . e(__('@deleted entry')) . '</span>';
                }

                return $this->renderUserPill($user, $key);
            },
            $text,
        ) ?? $text;
    }

    protected function renderMemberTokens(string $text, UserCollection $members): string
    {
        if ($members->isEmpty()) {
            return $text;
        }

        $byFirstname = $members->groupByFirstname()->all();
        $byCode = $members->keyByUserCode()->all();
        $userKey = morph_alias(User::class);

        return preg_replace_callback(
            '/(?<!\\\\)(?<![A-Za-z0-9._-])@([A-Za-z0-9._-]+)/',
            function (array $matches) use ($byFirstname, $byCode, $userKey): string {
                $token = strtolower($matches[1]);
                $user = ($byFirstname[$token] ?? null)?->first() ?? $byCode[$token] ?? null;
                if (is_null($user)) {
                    return $matches[0];
                }

                return $this->renderUserPill($user, $userKey);
            },
            $text,
        ) ?? $text;
    }

    protected function renderUserPill(Model $user, string $key): string
    {
        $id = (int) $user->getKey();
        $label = e($user->getMentionLabel());
        $url = $user->getMentionUrl();

        if (is_null($url)) {
            return sprintf(
                '<span class="mention mention--user" data-mention="%s:%d" data-user-id="%d">%s</span>',
                e($key),
                $id,
                $id,
                $label,
            );
        }

        return sprintf(
            '<a class="mention mention--user" href="%s" data-mention="%s:%d" data-user-id="%d">%s</a>',
            e($url),
            e($key),
            $id,
            $id,
            $label,
        );
    }
}
