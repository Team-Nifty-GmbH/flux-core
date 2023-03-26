<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;

class StockPosting extends Model
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $hidden = [
        'uuid',
    ];

    protected $casts = [
        'uuid' => 'string',
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];
}
