<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasPackageFactory, HasUuid;

    protected $guarded = [
        'id',
    ];
}
