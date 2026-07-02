<?php

namespace FluxErp\Console\Commands\Scout;

use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Laravel\Scout\Console\SyncIndexSettingsCommand as BaseSyncIndexSettingsCommand;
use Laravel\Scout\EngineManager;

class SyncIndexSettingsCommand extends BaseSyncIndexSettingsCommand
{
    protected $signature = 'flux-scout:sync-index-settings
                            {model? : The model class name you would like to sync the index settings for.}';

    /**
     * Execute the console command.
     */
    public function handle(EngineManager $manager): void
    {
        $engine = $manager->engine();

        $driver = config('scout.driver');

        if (! method_exists($engine, 'updateIndexSettings')) {
            $this->error('The "' . $driver . '" engine does not support updating index settings.');

            return;
        }

        $searchableModels = collect(Relation::morphMap())
            ->map(fn (string $class) => resolve_static($class, 'class'))
            ->filter(fn (string $class) => in_array(Searchable::class, class_uses_recursive($class))
                && method_exists($class, 'scoutIndexSettings')
            )
            ->unique()
            ->when(
                $this->argument('model'),
                fn (Collection $collection) => $collection->filter(
                    fn (string $class) => $class === $this->argument('model')
                )
            )
            ->values()
            ->toArray();

        foreach ($searchableModels as $model) {
            $settings = $model::scoutIndexSettings() ?? [];

            if (
                config('scout.soft_delete', false)
                && in_array(SoftDeletes::class, class_uses_recursive($model))
                && ! in_array('__soft_deleted', data_get($settings, 'filterableAttributes', []))
            ) {
                $settings['filterableAttributes'][] = '__soft_deleted';
            }

            if ($embedders = $model::scoutEmbedders()) {
                $settings['embedders'] = $embedders;
            }

            if (! $settings) {
                $this->info('No settings found for the [' . $model . '] model.');

                continue;
            }

            $indexName = $this->indexName($model);

            if (Arr::except($settings, 'embedders')) {
                $engine->updateIndexSettings($indexName, $settings);
            } else {
                // Meilisearch rejects an empty settings payload, sync the embedders directly.
                $engine->index($indexName)->updateEmbedders($settings['embedders']);
            }

            $this->info('Settings for the [' . $indexName . '] index synced successfully.');
        }
    }
}
