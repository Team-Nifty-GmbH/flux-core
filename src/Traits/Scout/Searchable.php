<?php

namespace FluxErp\Traits\Scout;

use FluxErp\Settings\SearchSettings;
use FluxErp\Support\Scout\ScoutCustomize;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable as BaseSearchable;
use Spatie\LaravelSettings\Exceptions\MissingSettings;

trait Searchable
{
    use BaseSearchable {
        BaseSearchable::search as protected baseScoutSearch;
    }

    public static function search($query = '', $callback = null): Builder
    {
        $builder = static::baseScoutSearch($query, $callback);

        // Semantic search: turn the keyword query into a hybrid (keyword + vector) query.
        if (config('scout.driver') === 'meilisearch' && $search = static::activeSearchSettings()) {
            // options() replaces, so merge to preserve any caller-provided options.
            $builder->options(array_merge($builder->options ?? [], [
                'hybrid' => [
                    'embedder' => 'default',
                    'semanticRatio' => $search->semantic_ratio,
                ],
            ]));
        }

        return $builder;
    }

    public static function scoutIndexSettings(): ?array
    {
        return config('scout.' . config('scout.driver') . '.index-settings.' . static::class) ?: null;
    }

    /**
     * The Meilisearch embedders derived from SearchSettings, or null when semantic search is off.
     */
    public static function scoutEmbedders(): ?array
    {
        if (config('scout.driver') !== 'meilisearch') {
            return null;
        }

        return ($search = static::activeSearchSettings())
            ? ['default' => static::embedderDefinition($search)]
            : null;
    }

    /**
     * The active SearchSettings when semantic search is usable, otherwise null.
     */
    protected static function activeSearchSettings(): ?SearchSettings
    {
        try {
            $search = app(SearchSettings::class);

            if (! $search->semantic_search_enabled || $search->embedder_url === '') {
                return null;
            }
        } catch (MissingSettings) {
            // Settings not migrated yet -> behave as if semantic search is off.
            return null;
        }

        return $search;
    }

    protected static function embedderDefinition(SearchSettings $search): array
    {
        return [
            'source' => 'rest',
            'url' => $search->embedder_url,
            'apiKey' => $search->embedder_api_key,
            // ponytail: dimensions must match the embedding model's output, customer-tunable knob
            'dimensions' => $search->embedder_dimensions,
            'request' => [
                'model' => $search->embedder_model,
                'input' => ['{{text}}', '{{..}}'],
            ],
            'response' => [
                'data' => [['embedding' => '{{embedding}}']],
            ],
        ];
    }

    public function toSearchableArray(): array
    {
        return ScoutCustomize::make($this)->toSearchableArray();
    }
}
