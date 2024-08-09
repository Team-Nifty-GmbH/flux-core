<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Snapshot extends Model
{
    use HasPackageFactory, HasUserModification, HasUuid, LogsActivity;

    protected $hidden = [
        'model_type',
    ];
}
