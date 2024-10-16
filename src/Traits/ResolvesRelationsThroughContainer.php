<?php

namespace FluxErp\Traits;

trait ResolvesRelationsThroughContainer
{
    protected function newRelatedInstance($class)
    {
        return parent::newRelatedInstance(resolve_static($class, 'class'));
    }

    protected function newRelatedThroughInstance($class)
    {
        return parent::newRelatedThroughInstance(resolve_static($class, 'class'));
    }
}
