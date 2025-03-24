<?php

namespace FluxErp\Traits;

use Illuminate\Database\ClassMorphViolationException;
use Illuminate\Database\Eloquent\Relations\Relation;

trait HasParentMorphClass
{
    public static function getParentMorphClass(): string
    {
        $morphMap = Relation::morphMap();

        $parentClass = get_parent_class(static::class);
        if (! empty($morphMap) && in_array($parentClass, $morphMap)) {
            return array_search($parentClass, $morphMap, true);
        }

        if (Relation::requiresMorphMap()) {
            throw new ClassMorphViolationException(new $parentClass());
        }

        return $parentClass;
    }

    public function getMorphClass(): string
    {
        try {
            return parent::getMorphClass();
        } catch (ClassMorphViolationException) {
            return static::getParentMorphClass();
        }
    }
}
