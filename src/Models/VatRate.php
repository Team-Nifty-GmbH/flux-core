<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use TeamNiftyGmbH\DataTable\Casts\Percentage;

class VatRate extends Model
{
    use CacheModelQueries, Filterable, HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'rate_percentage' => Percentage::class,
        ];
    }
}
