<?php

namespace FluxErp\Support\Broadcasting;

/**
 * The broadcaster Echo uses in the browser.
 */
class BrowserBroadcaster
{
    /**
     * Mercure whenever the instance broadcasts with it and the hub is reachable,
     * otherwise the other connection of a combined setup. Echo needs the driver, not the connection name.
     */
    public static function name(): string
    {
        $connection = config('broadcasting.default') ?? 'reverb';

        if (config('broadcasting.connections.' . $connection . '.driver') === 'combined') {
            $connection = resolve_static(MercureHubConfig::class, 'usesMercure') && static::servesHubHost()
                ? 'mercure'
                : array_first(
                    array_diff(config('broadcasting.connections.' . $connection . '.connections', []), ['mercure'])
                ) ?? 'null';
        }

        return config('broadcasting.connections.' . $connection . '.driver') ?? $connection;
    }

    public static function servesHubHost(): bool
    {
        $hubHost = parse_url((string) config('broadcasting.connections.mercure.public_url'), PHP_URL_HOST);

        return ! $hubHost || $hubHost === request()->getHost();
    }
}
