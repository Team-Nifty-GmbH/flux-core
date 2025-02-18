<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOptionGroup extends FluxModel
{
    use CacheModelQueries, Filterable, HasPackageFactory, HasUserModification, HasUuid, LogsActivity,
        SoftDeletes;

    public function productOptions(): HasMany
    {
        return $this->hasMany(ProductOption::class, 'product_option_group_id');
    }
}
