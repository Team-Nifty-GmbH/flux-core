<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasDefault;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends FluxModel
{
    use Filterable, HasDefault, HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'symbol' => 'string',
            'is_default' => 'boolean',
        ];
    }

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }
}
