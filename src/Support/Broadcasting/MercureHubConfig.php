<?php

namespace FluxErp\Support\Broadcasting;

/**
 * The Mercure hub built into FrankenPHP, configured through Octane's "octane.mercure" config.
 * Octane writes it into the Caddyfile when the server starts, so no separate hub process is needed.
 */
class MercureHubConfig
{
    /**
     * Hand the hub to Octane unless the instance configured its own.
     */
    public static function apply(): void
    {
        if (config('octane.mercure') || ! $hub = resolve_static(static::class, 'make')) {
            return;
        }

        config(['octane.mercure' => $hub]);
    }

    /**
     * The connection the browser listens on: Mercure whenever the instance broadcasts with it and the hub is reachable.
     */
    public static function browserBroadcaster(): string
    {
        $default = config('broadcasting.default') ?? 'reverb';
        $combined = config('broadcasting.connections.' . $default . '.driver') === 'combined';

        return match (true) {
            static::usesMercure() && (! $combined || static::servesHubHost()) => 'mercure',
            $combined => 'reverb',
            default => $default,
        };
    }

    public static function servesHubHost(): bool
    {
        $hubHost = parse_url((string) config('broadcasting.connections.mercure.public_url'), PHP_URL_HOST);

        return ! $hubHost || $hubHost === request()->getHost();
    }

    public static function make(): ?array
    {
        $secret = config('broadcasting.connections.mercure.secret');

        if (! $secret || ! static::usesMercure()) {
            return null;
        }

        // Mercure 1.0 syntax, needs FrankenPHP 1.13 or newer. The issuer must be the iss claim
        // Laravel puts into the tokens, which falls back to the app url as well.
        return array_filter([
            'issuer' => (config('broadcasting.connections.mercure.claims.iss') ?: config('app.url')) . " {\n"
                . "\t\t\t\tpublisher {\n\t\t\t\t\tjwt " . $secret . " HS256\n\t\t\t\t}\n"
                . "\t\t\t\tsubscriber {\n\t\t\t\t\tjwt " . $secret . " HS256\n\t\t\t\t}\n"
                . "\t\t\t}",
            'cors_origins' => config('app.url'),
            // Presence channels read their members from the subscriptions API.
            'subscriptions' => true,
            // The history lets a client that slept replay what it missed via Last-Event-ID.
            // storage/ survives a release, so the history does too.
            'transport' => "bolt {\n"
                . "\t\t\t\tpath " . storage_path('app/mercure.db') . "\n"
                . "\t\t\t\tsize " . config('flux.broadcasting.mercure_history_size', 10000) . "\n"
                . "\t\t\t\tcleanup_frequency 0.3\n"
                . "\t\t\t}",
            // Laravel and the hub have to agree on the cookie the subscriber token travels in.
            'cookie_name' => config('broadcasting.connections.mercure.cookie_name'),
        ]);
    }

    public static function usesMercure(): bool
    {
        $default = config('broadcasting.default');

        return $default === 'mercure'
            || (
                config('broadcasting.connections.' . $default . '.driver') === 'combined'
                && in_array('mercure', config('broadcasting.connections.' . $default . '.connections', []))
            );
    }
}
