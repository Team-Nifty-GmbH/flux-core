<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductPropertyGroup extends FluxModel
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    public function productProperties(): HasMany
    {
        return $this->hasMany(ProductProperty::class, 'product_property_group_id');
    }
}
