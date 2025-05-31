<?php

namespace FluxErp\Console\Commands;

use Illuminate\Database\Console\PruneCommand as BasePruneCommand;
use Illuminate\Database\Eloquent\Relations\Relation;

class PruneCommand extends BasePruneCommand
{
    protected function models()
    {
        $projectModels = parent::models();

        if (! empty($this->option('model'))) {
            return $projectModels;
        }

        return collect(Relation::morphMap())
            ->values()
            ->merge($projectModels)
            ->unique()
            ->values();
    }
}
