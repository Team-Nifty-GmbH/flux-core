<?php

namespace FluxErp\Traits;

use Illuminate\Database\ClassMorphViolationException;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\Relation;

trait HasParentMorphClass
{
    use HasRelationships;

    public function getMorphClass(): string
    {
        try {
            return parent::getMorphClass();
        } catch (ClassMorphViolationException) {
            return $this->getParentMorphClass();
        }
    }

    public function getParentMorphClass(): string
    {
        $morphMap = Relation::morphMap();

        $parentClass = get_parent_class($this);
        if (! empty($morphMap) && in_array($parentClass, $morphMap)) {
            return array_search($parentClass, $morphMap, true);
        }

        throw new ClassMorphViolationException($this);
    }
}
