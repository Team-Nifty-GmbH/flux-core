<?php

namespace FluxErp\Console\Commands\Scout;

use FluxErp\Traits\HasClientAssignment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Console\SyncIndexSettingsCommand as BaseSyncIndexSettingsCommand;
use Laravel\Scout\EngineManager;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class SyncIndexSettingsCommand extends BaseSyncIndexSettingsCommand
{
    protected $signature = 'scout:sync-index-settings
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

        try {
            $indexes = (array) config('scout.' . $driver . '.index-settings', []);

            if ($this->argument('model')) {
                $indexes = [$this->argument('model') => $indexes[$this->argument('model')] ?? []];
            }

            if (count($indexes)) {
                foreach ($indexes as $name => $settings) {
                    if (! is_array($settings)) {
                        $name = $settings;

                        $settings = [];
                    }

                    if (class_exists($name)) {
                        $model = app($name);
                    }

                    $uses = class_uses_recursive($model);
                    if (isset($model) &&
                        config('scout.soft_delete', false) &&
                        in_array(SoftDeletes::class, $uses)
                    ) {
                        $settings['filterableAttributes'][] = '__soft_deleted';
                    }

                    if (isset($model)
                        && in_array(HasClientAssignment::class, $uses)
                        && $model->isRelation('client')
                        && ($relation = $model->client()) instanceof BelongsTo
                    ) {
                        $settings['filterableAttributes'][] = $relation->getForeignKeyName();
                    }

                    if (isset($model) &&
                        array_key_exists('sortableAttributes', $settings)
                        && in_array('*', $settings['sortableAttributes'])
                        && $driver === 'meilisearch'
                    ) {
                        $settings['sortableAttributes'] = ModelInfo::forModel(get_class($model))
                            ->attributes
                            ->pluck('name')
                            ->toArray();
                        $settings['sortableAttributes'] = array_values(array_filter($settings['sortableAttributes']));
                    }

                    $engine->updateIndexSettings($indexName = $this->indexName($name), $settings);

                    $this->info('Settings for the [' . $indexName . '] index synced successfully.');
                }
            } else {
                $this->info('No index settings found for the "' . $driver . '" engine.');
            }
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}
