<?php

namespace FluxErp\Traits;

use Illuminate\Database\Eloquent\Model;

trait ResolvesRelationsThroughContainer
{
    protected function newRelatedInstance($class): Model
    {
        return parent::newRelatedInstance(resolve_static($class, 'class'));
    }

    protected function newRelatedThroughInstance($class): Model
    {
        return parent::newRelatedThroughInstance(resolve_static($class, 'class'));
    }
}
