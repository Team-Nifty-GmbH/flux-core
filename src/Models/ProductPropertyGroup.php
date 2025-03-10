<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductPropertyGroup extends FluxModel
{
    use CacheModelQueries, Filterable, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    public function productProperties(): HasMany
    {
        return $this->hasMany(ProductProperty::class, 'product_property_group_id');
    }
}
