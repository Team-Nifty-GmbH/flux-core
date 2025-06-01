<?php

namespace FluxErp\Console\Commands;

use Illuminate\Database\Console\PruneCommand as BasePruneCommand;
use Illuminate\Database\Eloquent\Relations\Relation;

class PruneCommand extends BasePruneCommand
{
    protected function models()
    {
        $models = parent::models();

        if (! empty($this->option('model'))) {
            return $models;
        }

        return collect(Relation::morphMap())
            ->values()
            ->merge($models)
            ->unique()
            ->values();
    }
}
