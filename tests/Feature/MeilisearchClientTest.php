<?php

use FluxErp\FluxServiceProvider;
use FluxErp\Models\Category;
use FluxErp\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Laravel\Scout\Jobs\MakeSearchable;
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

test('scout index updates wait for the commit', function (): void {
    Queue::fake();

    DB::transaction(function (): void {
        Category::factory()->create(['model_type' => morph_alias(Product::class)]);

        Queue::assertNotPushed(MakeSearchable::class);
    });

    Queue::assertPushed(MakeSearchable::class);
});
