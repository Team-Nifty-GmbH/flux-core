<?php

use FluxErp\Models\Country;
use FluxErp\Settings\SearchSettings;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\MeilisearchEngine;

function mockScoutEngine(): MeilisearchEngine|Mockery\MockInterface
{
    $engine = Mockery::mock(MeilisearchEngine::class);

    test()->mock(EngineManager::class)
        ->shouldReceive('engine')
        ->andReturn($engine);

    return $engine;
}

beforeEach(function (): void {
    config(['scout.driver' => 'meilisearch']);
});

test('skips models without index settings when semantic search is disabled', function (): void {
    SearchSettings::fake(['semantic_search_enabled' => false]);

    $engine = mockScoutEngine();
    $engine->shouldNotReceive('updateIndexSettings');
    $engine->shouldNotReceive('index');

    $this->artisan('flux-scout:sync-index-settings', ['model' => Country::class])
        ->assertExitCode(0);
});

test('syncs embedders without sending an empty settings payload', function (): void {
    SearchSettings::fake([
        'semantic_search_enabled' => true,
        'embedder_url' => 'https://litellm.test/v1/embeddings',
        'embedder_api_key' => 'sk-test',
        'embedder_model' => 'text-embedding-3-small',
        'embedder_dimensions' => 1536,
    ]);

    $engine = mockScoutEngine();
    $engine->shouldNotReceive('updateIndexSettings');
    $engine->shouldReceive('index')
        ->once()
        ->with(app(Country::class)->indexableAs())
        ->andReturn($index = Mockery::mock());
    $index->shouldReceive('updateEmbedders')
        ->once()
        ->withArgs(fn (array $embedders) => data_get($embedders, 'default.url')
            === 'https://litellm.test/v1/embeddings'
        );

    $this->artisan('flux-scout:sync-index-settings', ['model' => Country::class])
        ->assertExitCode(0);
});

test('syncs keyword settings and embedders together through the engine', function (): void {
    config([
        'scout.meilisearch.index-settings.' . Country::class => [
            'filterableAttributes' => ['iso_alpha2'],
        ],
    ]);

    SearchSettings::fake([
        'semantic_search_enabled' => true,
        'embedder_url' => 'https://litellm.test/v1/embeddings',
        'embedder_api_key' => 'sk-test',
        'embedder_model' => 'text-embedding-3-small',
        'embedder_dimensions' => 1536,
    ]);

    $engine = mockScoutEngine();
    $engine->shouldReceive('updateIndexSettings')
        ->once()
        ->withArgs(fn (string $index, array $settings) => data_get($settings, 'filterableAttributes') === ['iso_alpha2']
            && data_get($settings, 'embedders.default.source') === 'rest'
        );

    $this->artisan('flux-scout:sync-index-settings', ['model' => Country::class])
        ->assertExitCode(0);
});
