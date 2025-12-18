<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;

class Snapshot extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid, LogsActivity;

    protected $hidden = [
        'model_type',
    ];
}
