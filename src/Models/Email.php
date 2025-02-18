<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @deprecated
 */
class Email extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'to' => 'array',
            'cc' => 'array',
            'bcc' => 'array',
            'view_data' => 'object',
        ];
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
