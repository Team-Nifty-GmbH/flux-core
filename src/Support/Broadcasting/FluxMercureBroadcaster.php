<?php

namespace FluxErp\Support\Broadcasting;

use Illuminate\Broadcasting\Broadcasters\MercureBroadcaster;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Mercure\Jwt\Grant;

class FluxMercureBroadcaster extends MercureBroadcaster
{
    public static function fromBroadcaster(MercureBroadcaster $broadcaster): static
    {
        return new static(
            $broadcaster->hub,
            $broadcaster->expiration,
            $broadcaster->encrypter,
            $broadcaster->topicPrefix,
            $broadcaster->clientEvents,
        );
    }

    public function auth($request)
    {
        $requested = (array) $request->input('channel_names', []);

        if (
            ! $request->hasSession()
            || $requested === []
            || count($requested) > 100
            || $requested !== array_filter($requested, 'is_string')
        ) {
            return parent::auth($request);
        }

        $key = 'mercure-channels:' . $request->session()->getId();
        $now = time();
        $known = array_filter(
            Cache::get($key, []),
            fn (int $seen): bool => $seen >= $now - 2 * $this->expiration
        );

        foreach ($requested as $channel) {
            $known[$channel] = $now;
        }

        $merged = array_slice(
            array_values(array_unique(array_merge($requested, array_keys($known)))),
            0,
            100
        );

        $request->merge(['channel_names' => $merged]);
        $response = parent::auth($request);
        $request->merge(['channel_names' => $requested]);

        $data = $response->getData(true);
        $channels = data_get($data, 'channel_names') ?? [];

        foreach ($channels as $channel) {
            if (data_get($channel, 'denied')) {
                unset($known[data_get($channel, 'name')]);
            }
        }

        Cache::put($key, array_intersect_key($known, array_flip($merged)), 3 * $this->expiration);

        $data['channel_names'] = array_values(array_filter(
            $channels,
            fn (array $channel): bool => in_array(data_get($channel, 'name'), $requested, true)
        ));

        return $response->setData($data);
    }

    protected function compactGrant(Grant $grant): Grant
    {
        $isList = array_is_list($grant->topics);
        [$exact, $patterns] = $this->compactTopics($isList ? $grant->topics : data_get($grant->topics, 'exact') ?? []);
        $patterns = array_merge(
            $patterns,
            $isList ? [] : $this->compactSubscriptionPatterns(data_get($grant->topics, 'urlpattern') ?? [])
        );

        if ($isList && $patterns === []) {
            return $grant;
        }

        return new Grant(
            $grant->actions,
            array_filter(['exact' => $exact, 'urlpattern' => $patterns]),
            $grant->payload
        );
    }

    protected function compactSubscriptionPatterns(array $patterns): array
    {
        $groups = [];
        $compact = [];

        foreach ($patterns as $pattern) {
            if (preg_match('/^(.+\.)([A-Za-z0-9_-]+)\{\/:subscriber\}\?$/', $pattern, $match)) {
                $groups[$match[1]][] = $match[2];
            } else {
                $compact[] = $pattern;
            }
        }

        foreach ($groups as $prefix => $ids) {
            $compact[] = $prefix
                . (count($ids) === 1 ? $ids[0] : ':id(' . implode('|', array_unique($ids)) . ')')
                . '{/:subscriber}?';
        }

        return $compact;
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, string>} exact topics and url patterns
     */
    protected function compactTopics(array $topics): array
    {
        $groups = [];
        $exact = [];

        foreach ($topics as $topic) {
            if (preg_match('/^(.+\.)([A-Za-z0-9_-]+)$/', $topic, $match)) {
                $groups[$match[1]]['ids'][] = $match[2];
            } elseif (preg_match('/^(.+-)([A-Za-z0-9_]+)\.$/', $topic, $match)) {
                $groups[$match[1]]['names'][] = $match[2];
            } else {
                $exact[] = $topic;
            }
        }

        $patterns = [];

        foreach ($groups as $prefix => $group) {
            $ids = data_get($group, 'ids') ?? [];
            $names = data_get($group, 'names') ?? [];

            if (count($ids) === 1) {
                $exact[] = $prefix . $ids[0];
            } elseif ($ids !== []) {
                $patterns[] = $this->escapePattern($prefix) . ':id(' . implode('|', array_unique($ids)) . ')';
            }

            if (count($names) === 1) {
                $exact[] = $prefix . $names[0] . '.';
            } elseif ($names !== []) {
                $patterns[] = $this->escapePattern($prefix) . ':name(' . implode('|', array_unique($names)) . ').';
            }
        }

        return [$exact, $patterns];
    }

    protected function escapePattern(string $value): string
    {
        [$scheme, $rest] = explode('://', $value, 2) + [1 => ''];

        return $scheme . '://' . preg_replace('/([:*(){}?+\\\\])/', '\\\\$1', $rest);
    }

    protected function makeAuthorizationCookie($request, array $grants, $user = null)
    {
        return parent::makeAuthorizationCookie(
            $request,
            array_map(fn (Grant $grant): Grant => $this->compactGrant($grant), $this->mergePresenceGrants($grants)),
            $user
        );
    }

    /**
     * Presence channels get one grant each. Channels with the same member data can
     * share one, which is what keeps the token small enough for a browser cookie.
     */
    protected function mergePresenceGrants(array $grants): array
    {
        $merged = [];

        foreach ($grants as $index => $grant) {
            if (array_is_list($grant->topics)) {
                $merged[$index] = $grant;

                continue;
            }

            $key = json_encode([$grant->actions, $grant->payload]);
            $existing = $merged[$key] ?? null;

            $merged[$key] = new Grant(
                $grant->actions,
                $existing ? array_merge_recursive($existing->topics, $grant->topics) : $grant->topics,
                $grant->payload
            );
        }

        return array_values($merged);
    }
}
