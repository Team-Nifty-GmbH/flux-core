<?php

namespace FluxErp\Console\Commands\Scout;

use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Scout\Console\FlushCommand as BaseFlushCommand;
use Laravel\Scout\Searchable;

class FlushCommand extends BaseFlushCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flux-scout:flush
            {model? : Class name of model to flush}';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $models = (array) $this->argument('model') ?:
            collect(Relation::morphMap())
                ->map(fn (string $class) => resolve_static($class, 'class'))
                ->filter(fn (string $class) => in_array(Searchable::class, class_uses_recursive($class)))
                ->unique()
                ->values()
                ->toArray();

        foreach ($models as $model) {
            $this->call(BaseFlushCommand::class, ['model' => $model]);
        }

        return 0;
    }
}
