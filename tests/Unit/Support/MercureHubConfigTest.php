<?php

use FluxErp\Support\Broadcasting\MercureHubConfig;

beforeEach(function (): void {
    config([
        'app.url' => 'https://flux.example.com',
        'broadcasting.connections.mercure.secret' => str_repeat('s', 32),
        'broadcasting.connections.combined.connections' => ['mercure', 'reverb'],
    ]);
});

test('the hub keeps a history so missed events can be replayed', function (string $default): void {
    config(['broadcasting.default' => $default]);

    $hub = MercureHubConfig::make();

    expect($hub['issuer'])->toStartWith('https://flux.example.com {')
        ->and($hub['issuer'])->toContain('publisher {')
        ->and($hub['issuer'])->toContain('subscriber {')
        ->and(substr_count($hub['issuer'], 'jwt ' . str_repeat('s', 32) . ' HS256'))->toBe(2)
        ->and($hub)->not->toHaveKeys(['publisher_jwt', 'subscriber_jwt'])
        ->and($hub['cors_origins'])->toBe('https://flux.example.com')
        ->and($hub['subscriptions'])->toBeTrue()
        ->and($hub['transport'])->toContain('bolt {')
        ->and($hub['transport'])->toContain('path ' . storage_path('app/mercure.db'))
        ->and($hub['transport'])->toContain('size 10000');
})->with(['mercure', 'combined']);

test('no hub is configured without mercure', function (): void {
    config([
        'broadcasting.default' => 'combined',
        'broadcasting.connections.combined.connections' => ['reverb'],
    ]);

    expect(MercureHubConfig::make())->toBeNull();

    config(['broadcasting.default' => 'reverb']);

    expect(MercureHubConfig::make())->toBeNull();
});

test('no hub is configured without a secret', function (): void {
    config([
        'broadcasting.default' => 'mercure',
        'broadcasting.connections.mercure.secret' => null,
    ]);

    expect(MercureHubConfig::make())->toBeNull();
});

test('octane receives the hub unless the instance configured its own', function (): void {
    config(['broadcasting.default' => 'mercure', 'octane.mercure' => null]);

    MercureHubConfig::apply();

    expect(config('octane.mercure.subscriptions'))->toBeTrue();

    config(['octane.mercure' => ['anonymous' => true]]);

    MercureHubConfig::apply();

    expect(config('octane.mercure'))->toBe(['anonymous' => true]);
});

test('the hub trusts the issuer laravel puts into the tokens', function (): void {
    config([
        'broadcasting.default' => 'mercure',
        'broadcasting.connections.mercure.claims.iss' => null,
    ]);

    expect(MercureHubConfig::make()['issuer'])->toStartWith('https://flux.example.com {');

    config(['broadcasting.connections.mercure.claims.iss' => 'https://issuer.example.com']);

    expect(MercureHubConfig::make()['issuer'])->toStartWith('https://issuer.example.com {');
});

test('the hub reads the token from the cookie laravel sets', function (): void {
    config([
        'broadcasting.default' => 'mercure',
        'broadcasting.connections.mercure.cookie_name' => 'mercure_access_token',
    ]);

    expect(MercureHubConfig::make()['cookie_name'])->toBe('mercure_access_token');

    config(['broadcasting.connections.mercure.cookie_name' => null]);

    expect(MercureHubConfig::make())->not->toHaveKey('cookie_name');
});
