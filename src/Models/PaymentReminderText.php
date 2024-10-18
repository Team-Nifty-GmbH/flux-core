<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;

class PaymentReminderText extends FluxModel
{
    use CacheModelQueries, HasPackageFactory, HasUuid;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'mail_to' => 'array',
            'mail_cc' => 'array',
        ];
    }
}
