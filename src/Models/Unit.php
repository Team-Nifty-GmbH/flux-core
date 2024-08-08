<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use Filterable, HasPackageFactory, HasUuid, LogsActivity, SoftDeletes;

    protected $guarded = [
        'id',
    ];
}
