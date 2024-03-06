<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @deprecated
 */
class Email extends Model
{
    use HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $casts = [
        'uuid' => 'string',
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'view_data' => 'object',
    ];

    protected $guarded = [
        'id',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
