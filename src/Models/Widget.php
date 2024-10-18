<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;

class Widget extends FluxModel
{
    use HasPackageFactory, HasUuid;

    protected $guarded = [
        'id',
    ];
}
