<?php

namespace FluxErp\Console\Commands\Scout;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Scout\Console\ImportCommand as BaseImportCommand;
use Laravel\Scout\Searchable;

class ImportCommand extends BaseImportCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flux-scout:import
            {model? : Class name of model to bulk import}
            {--c|chunk= : The number of records to import at a time (Defaults to configuration value: `scout.chunk.searchable`)}';

    /**
     * Execute the console command.
     */
    public function handle(Dispatcher $events): int
    {
        $models = (array) $this->argument('model') ?:
            collect(Relation::morphMap())
                ->map(fn (string $class) => resolve_static($class, 'class'))
                ->filter(fn (string $class) => in_array(Searchable::class, class_uses_recursive($class)))
                ->unique()
                ->values()
                ->toArray();

        foreach ($models as $model) {
            $this->call(
                BaseImportCommand::class,
                [
                    'model' => $model,
                    '--chunk' => $this->option('chunk'),
                ]
            );
        }

        return 0;
    }
}
