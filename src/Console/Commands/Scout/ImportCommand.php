<?php

namespace FluxErp\Console\Commands\Scout;

use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Scout\Console\ImportCommand as BaseImportCommand;
use Laravel\Scout\Searchable;
use Spatie\ModelInfo\ModelFinder;

class ImportCommand extends BaseImportCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:import
            {model? : Class name of model to bulk import}
            {--c|chunk= : The number of records to import at a time (Defaults to configuration value: `scout.chunk.searchable`)}';

    /**
     * Execute the console command.
     */
    public function handle(Dispatcher $events): int
    {
        ModelFinder::all(flux_path('src/Models'), flux_path('src'), 'FluxErp')->merge(
            ModelFinder::all()
        );
        $models = (array) $this->argument('model') ?:
            array_values(
                ModelFinder::all(flux_path('src/Models'), flux_path('src'), 'FluxErp')
                    ->merge(ModelFinder::all())
                    ->filter(fn ($model) => in_array(Searchable::class, class_uses_recursive($model)))
                    ->unique()
                    ->toArray()
            );

        foreach ($models as $model) {
            $this->call(BaseImportCommand::class, ['model' => $model]);
        }

        return 0;
    }
}
