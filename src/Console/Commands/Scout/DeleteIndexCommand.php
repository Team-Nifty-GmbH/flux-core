<?php

namespace FluxErp\Console\Commands\Scout;

use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Scout\Console\DeleteIndexCommand as BaseDeleteIndexCommand;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Searchable;

class DeleteIndexCommand extends BaseDeleteIndexCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flux-scout:delete-index
            {name? : The name of the index}';

    /**
     * Execute the console command.
     */
    public function handle(EngineManager $manager): int
    {
        $indexes = (array) $this->argument('name') ?:
            collect(Relation::morphMap())
                ->map(fn (string $class) => resolve_static($class, 'class'))
                ->filter(fn (string $class) => in_array(Searchable::class, class_uses_recursive($class)))
                ->map(fn (string $model) => app($model)->searchableAs())
                ->unique()
                ->values()
                ->toArray();

        foreach ($indexes as $indexName) {
            $this->call(BaseDeleteIndexCommand::class, ['name' => $indexName]);
        }

        return 0;
    }
}
