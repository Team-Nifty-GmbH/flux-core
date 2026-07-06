<?php

namespace FluxErp\Support\VariantInheritance;

use FluxErp\Models\Product;

class InheritanceSync
{
    public static function changedInheritableFields(Product $parent): array
    {
        return array_values(array_intersect(
            array_keys($parent->getDirty()),
            $parent->getInheritableFields()
        ));
    }
}
