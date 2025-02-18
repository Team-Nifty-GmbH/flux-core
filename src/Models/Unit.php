<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends FluxModel
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
