<?php

namespace FluxErp\Console\Commands\Scout;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Console\DeleteIndexCommand as BaseDeleteIndexCommand;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Searchable;
use Spatie\ModelInfo\ModelFinder;

class DeleteIndexCommand extends BaseDeleteIndexCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:delete-index
            {name? : The name of the index}';

    /**
     * Execute the console command.
     */
    public function handle(EngineManager $manager): int
    {
        $indexes = (array) $this->argument('name') ?:
            array_values(
                ModelFinder::all(flux_path('src/Models'), flux_path('src'), 'FluxErp')
                    ->merge(ModelFinder::all())
                    ->filter(fn ($model) => in_array(Searchable::class, class_uses_recursive($model)))
                    ->map(fn ($model) => (new $model)->searchableAs())->unique()->toArray()
            );

        foreach ($indexes as $index) {
            $indexName = $index instanceof Model ? $index->searchableAs() : $index;
            $this->call(BaseDeleteIndexCommand::class, ['name' => $indexName]);
        }

        return 0;
    }
}
