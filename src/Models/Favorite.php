<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasUuid;

    protected $guarded = [
        'id',
    ];
}
