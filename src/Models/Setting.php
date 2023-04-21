<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Setting extends Model
{
    use HasPackageFactory, HasUuid;

    protected $casts = [
        'uuid' => 'string',
        'settings' => 'array',
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
