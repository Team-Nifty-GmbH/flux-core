<?php

namespace FluxErp\Console\Commands\Scout;

use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Scout\Console\IndexCommand as BaseIndexCommand;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Searchable;

class IndexCommand extends BaseIndexCommand
{
    protected $signature = 'flux-scout:index
            {name? : Class name of model to bulk import}
            {--k|key= : The name of the primary key}';

    public function handle(EngineManager $manager): void
    {
        $models = (array) $this->argument('name') ?:
            collect(Relation::morphMap())
                ->map(fn (string $class) => resolve_static($class, 'class'))
                ->filter(fn (string $class) => in_array(Searchable::class, class_uses_recursive($class)))
                ->unique()
                ->values()
                ->toArray();

        foreach ($models as $model) {
            $this->call(BaseIndexCommand::class, ['name' => $model, '--key' => $this->option('key')]);
        }
    }
}
