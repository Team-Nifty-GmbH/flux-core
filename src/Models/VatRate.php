<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class VatRate extends Model
{
    use CacheModelQueries, Filterable, HasPackageFactory, HasUuid, LogsActivity, SoftDeletes;

    protected $guarded = [
        'id',
    ];
}
