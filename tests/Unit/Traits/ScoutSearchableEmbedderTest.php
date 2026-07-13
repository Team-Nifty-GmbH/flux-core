<?php

use FluxErp\Models\Country;
use FluxErp\Settings\SearchSettings;

test('provides meilisearch embedders when semantic search enabled', function (): void {
    config(['scout.driver' => 'meilisearch']);

    SearchSettings::fake([
        'semantic_search_enabled' => true,
        'embedder_url' => 'https://litellm.test/v1/embeddings',
        'embedder_api_key' => 'sk-test',
        'embedder_model' => 'text-embedding-3-small',
        'embedder_dimensions' => 1536,
        'semantic_ratio' => 0.5,
    ]);

    $embedder = data_get(Country::scoutEmbedders(), 'default');

    expect($embedder)->toMatchArray([
        'source' => 'rest',
        'url' => 'https://litellm.test/v1/embeddings',
        'apiKey' => 'sk-test',
        'dimensions' => 1536,
    ]);
    expect(data_get($embedder, 'request.model'))->toBe('text-embedding-3-small');
});

test('provides no embedders when semantic search disabled', function (): void {
    config(['scout.driver' => 'meilisearch']);

    SearchSettings::fake([
        'semantic_search_enabled' => false,
        'embedder_url' => 'https://litellm.test/v1/embeddings',
    ]);

    expect(Country::scoutEmbedders())->toBeNull();
});

test('returns null index settings when nothing is configured', function (): void {
    config(['scout.driver' => 'meilisearch']);

    SearchSettings::fake(['semantic_search_enabled' => false]);

    expect(Country::scoutIndexSettings())->toBeNull();
});

test('adds hybrid search options when semantic search enabled', function (): void {
    config(['scout.driver' => 'meilisearch']);

    SearchSettings::fake([
        'semantic_search_enabled' => true,
        'embedder_url' => 'https://litellm.test/v1/embeddings',
        'semantic_ratio' => 0.7,
    ]);

    $builder = Country::search('berlin');

    expect(data_get($builder->options, 'hybrid'))->toMatchArray([
        'embedder' => 'default',
        'semanticRatio' => 0.7,
    ]);
});

test('adds no hybrid options when semantic search disabled', function (): void {
    config(['scout.driver' => 'meilisearch']);

    SearchSettings::fake(['semantic_search_enabled' => false]);

    expect(data_get(Country::search('berlin')->options, 'hybrid'))->toBeNull();
});
