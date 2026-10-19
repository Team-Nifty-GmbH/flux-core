<?php

use FluxErp\FluxServiceProvider;
use Meilisearch\Client;

function meilisearchHttpClient(Client $client): object
{
    $adapter = (new ReflectionProperty($client, 'http'))->getValue($client);

    return (new ReflectionProperty($adapter, 'http'))->getValue($adapter);
}

test('meilisearch client gives up after the configured timeout', function (): void {
    config(['flux.meilisearch.timeout' => 7]);
    app()->forgetInstance(Client::class);

    expect(meilisearchHttpClient(app(Client::class))->getConfig('timeout'))->toBe(7);
});

test('meilisearch timeout defaults to three seconds', function (): void {
    expect(config('flux.meilisearch.timeout'))->toBe(3);
});

test('scout queues index updates by default', function (): void {
    config(['scout.queue' => false]);
    app()->instance('config_loaded_from_cache', false);

    (new ReflectionMethod(FluxServiceProvider::class, 'registerConfig'))
        ->invoke(new FluxServiceProvider(app()));

    expect(config('scout.queue'))->toBeTrue();
});
