<?php

namespace FluxErp\Console\Commands\Scout;

use Laravel\Scout\Console\FlushCommand as BaseFlushCommand;
use Laravel\Scout\Searchable;
use Spatie\ModelInfo\ModelFinder;

class FlushCommand extends BaseFlushCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:flush
            {model? : Class name of model to flush}';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $models = (array) $this->argument('model') ?:
            array_values(
                ModelFinder::all(flux_path('src/Models'), flux_path('src'), 'FluxErp')
                    ->merge(ModelFinder::all())
                    ->filter(fn ($model) => in_array(Searchable::class, class_uses_recursive($model)))
                    ->unique()
                    ->toArray()
            );

        foreach ($models as $model) {
            $this->call(BaseFlushCommand::class, ['model' => $model]);
        }

        return 0;
    }
}
