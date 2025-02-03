<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;

/**
 * @deprecated
 */
class EmailTemplate extends FluxModel
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
}
