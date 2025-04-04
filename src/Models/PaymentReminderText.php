<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\HasAttributeTranslations;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;

class PaymentReminderText extends FluxModel
{
    use CacheModelQueries, HasAttributeTranslations, HasPackageFactory, HasUuid;

    protected function casts(): array
    {
        return [
            'mail_to' => 'array',
            'mail_cc' => 'array',
        ];
    }

    protected function translatableAttributes(): array
    {
        return [
            'reminder_subject',
            'reminder_body',
        ];
    }
}
