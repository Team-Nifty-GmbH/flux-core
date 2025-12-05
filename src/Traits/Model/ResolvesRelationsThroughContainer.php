<?php

namespace FluxErp\Traits\Model;

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
