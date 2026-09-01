<?php

namespace FluxErp\Support\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo as EloquentMorphTo;

class MorphTo extends EloquentMorphTo
{
    public function createModelByType($type): Model
    {
        $class = resolve_static(Model::getActualClassNameForMorph($type), 'class');

        return tap(new $class(), function ($instance): void {
            if (! $instance->getConnectionName()) {
                $instance->setConnection($this->getConnection()->getName());
            }
        });
    }
}
