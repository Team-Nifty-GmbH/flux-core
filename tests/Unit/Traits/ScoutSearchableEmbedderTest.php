<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Communication;
use FluxErp\Models\Country;
use FluxErp\Models\Order;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Models\User;
use FluxErp\Settings\SearchSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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

test('mirrors the batch repetition marker in request and response', function (): void {
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

    // Meilisearch requires the '{{..}}' marker in both request and response when
    // batching; a request-only marker is rejected with "response has a single
    // embedding, but request has multiple texts to embed".
    expect(data_get($embedder, 'request.input'))->toBe(['{{text}}', '{{..}}'])
        ->and(data_get($embedder, 'response.data'))->toBe([['embedding' => '{{embedding}}'], '{{..}}']);
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

test('adds the ranking score threshold to hybrid options when configured', function (): void {
    config(['scout.driver' => 'meilisearch']);

    SearchSettings::fake([
        'semantic_search_enabled' => true,
        'embedder_url' => 'https://litellm.test/v1/embeddings',
        'semantic_ratio' => 0.5,
        'semantic_score_threshold' => 0.78,
    ]);

    expect(data_get(Country::search('berlin')->options, 'rankingScoreThreshold'))->toBe(0.78);
});

test('omits the ranking score threshold when it is zero', function (): void {
    config(['scout.driver' => 'meilisearch']);

    SearchSettings::fake([
        'semantic_search_enabled' => true,
        'embedder_url' => 'https://litellm.test/v1/embeddings',
        'semantic_ratio' => 0.5,
        'semantic_score_threshold' => 0,
    ]);

    expect(data_get(Country::search('berlin')->options, 'rankingScoreThreshold'))->toBeNull();
});

/**
 * The fields a model's template reads that would make Meilisearch fail the whole batch: a doc.<path> the document
 * Scout really indexes does not have, or a nested path whose parent is not guarded by {% if doc.<parent> %}, which
 * fails with "Unknown index" as soon as that parent is null on one record.
 */
function unsafeTemplatePaths(string $class, Model $model): array
{
    config(['scout.driver' => 'meilisearch']);
    SearchSettings::fake([
        'semantic_search_enabled' => true,
        'embedder_url' => 'https://litellm.test/v1/embeddings',
        'embedder_model' => 'text-embedding-3-small',
        'embedder_dimensions' => 1536,
    ]);

    $embedder = data_get($class::scoutEmbedders(), 'default');
    $template = (string) data_get($embedder, 'documentTemplate');
    preg_match_all('/doc\.([\w.]+)/', $template, $matches);
    $document = $model->toSearchableArray();

    expect($matches[1])->not->toBeEmpty()
        ->and(data_get($embedder, 'documentTemplateMaxBytes'))->toBe($class::EMBEDDER_DOCUMENT_TEMPLATE_MAX_BYTES);

    return array_values(array_filter(
        array_unique($matches[1]),
        fn (string $path): bool => ! Arr::has($document, $path)
            || (str_contains($path, '.') && ! str_contains($template, '{% if doc.' . Str::beforeLast($path, '.') . ' %}'))
    ));
}

test('the ticket template reads only fields the ticket document has, the type guarded', function (): void {
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => User::factory()->create()->getKey(),
        'ticket_type_id' => TicketType::factory()->create()->getKey(),
    ]);

    expect(unsafeTemplatePaths(Ticket::class, $ticket))->toBe([]);
});

test('the order template reads only fields the order document has', function (): void {
    expect(unsafeTemplatePaths(Order::class, makeForecastOrder(OrderTypeEnum::Order, '10.00')))->toBe([]);
});

test('the communication template reads only fields the communication document has', function (): void {
    expect(unsafeTemplatePaths(Communication::class, Communication::factory()->create()))->toBe([]);
});

test('a model without a template keeps the Meilisearch default', function (): void {
    config(['scout.driver' => 'meilisearch']);
    SearchSettings::fake([
        'semantic_search_enabled' => true,
        'embedder_url' => 'https://litellm.test/v1/embeddings',
    ]);

    expect(data_get(Country::scoutEmbedders(), 'default'))->not->toHaveKeys(['documentTemplate', 'documentTemplateMaxBytes']);
});
