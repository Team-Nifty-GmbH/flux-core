<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Snapshot extends Model
{
    use HasPackageFactory, HasUserModification, HasUuid;

    protected $hidden = [
        'model_type',
    ];

    protected $casts = [
        'uuid' => 'string',
    ];
}
