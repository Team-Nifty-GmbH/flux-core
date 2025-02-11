<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasDefault;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VatRate extends FluxModel
{
    use CacheModelQueries, Filterable, HasDefault, HasPackageFactory, HasUserModification, HasUuid, LogsActivity,
        SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_tax_exemption' => 'boolean',
        ];
    }

    public function orderPositions(): HasMany
    {
        return $this->hasMany(OrderPosition::class);
    }
}
