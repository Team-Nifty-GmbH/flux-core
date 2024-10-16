<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;

class Unit extends FluxModel
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected $guarded = [
        'id',
    ];
}
